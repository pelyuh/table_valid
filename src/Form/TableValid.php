<?php

namespace Drupal\table_valid\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

class TableValid extends FormBase
{
    /**
     * @return string
     *  The unique string identifying the form.
     */
    // Метод, який буде повертати назву форми
    public function getFormId()
    {
        return 'valid_form';
    }

    private function table_head_name()
    {
        $table_head_name = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
            13 => 13,
            14 => 14,
            15 => 15,
            16 => 16,
        ];
        return $table_head_name;
    }

    // Метод добавлення таблиці
    public function addTable(array &$form, FormStateInterface $form_state)
    {
        $table_number = $form_state->get('table_number');
        $table_number++;

        $row_number = $form_state->get('row_number');
        $row_number['table_' . $table_number] = 1;

        $form_state->set('row_number', $row_number);
        $form_state->set('table_number', $table_number);

        $form_state->setRebuild();
    }

    // Метод добавлення рядка
    public function addRow(array &$form, FormStateInterface $form_state)
    {
        $triggering_element = $form_state->getTriggeringElement();
        $table = $triggering_element['#post'];
//        $table = trim(substr($_POST['op'], 8));

        $row_number = $form_state->get('row_number');
        $row_number[$table]++;

        $form_state->set('row_number', $row_number);

        $form_state->setRebuild();
    }

    // Білдінг форми
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $table_number = $form_state->get('table_number');
        $row_number = $form_state->get('row_number');

        if (empty($table_number)) {
            $table_number = 1;
            $form_state->set('table_number', $table_number);
        }

        if (empty($row_number)) {
            $row_number['table_' . $table_number] = 1;
            $form_state->set('row_number', $row_number);
        }

        for ($j = 1; $j <= $table_number; $j++) {


            $table = 'table_' . $j;

            // Рендер кнопки Add Year
            $form['add_row_' . $table] = [
                '#type' => 'submit',
                '#value' => $this->t('Add Year       ' . $table),
                '#submit' => ['::addRow'],
                '#post' => $table,
                '#attributes' => ['class' => ['button-add-row']],
            ];

            // Рендер шапки таблиці
            $form[$table] = [
                '#type' => 'table',
                '#header' => [
                    $this->t('Year'),
                    $this->t('Jan'),
                    $this->t('Feb'),
                    $this->t('Mar'),
                    $this->t('Q1'),
                    $this->t('Apr'),
                    $this->t('May'),
                    $this->t('Jun'),
                    $this->t('Q2'),
                    $this->t('Jul'),
                    $this->t('Aug'),
                    $this->t('Sep'),
                    $this->t('Q3'),
                    $this->t('Oct'),
                    $this->t('Nov'),
                    $this->t('Dec'),
                    $this->t('Q4'),
                    $this->t('YTD'),
                ],
            ];

            //Добавлення рядків
            for ($row = $row_number[$table]; $row > 0; $row--) {

                $year = date('Y') + 1;

                $form[$table][$row]['Year'] = [
                    '#type' => 'html_tag',
                    '#tag' => 'strong',
                    '#value' => $year - $row,
                ];

                $table_cell_name = $this->table_head_name();

                for ($cell = 1; $cell <= count($table_cell_name); $cell++) {

                    $id_cell = $j . '_' . $row . '_' . $cell;

                    if (isset($_POST[$id_cell])) {

                        $value = $_POST[$id_cell];

                    } else {

                        $value = '';
                    }


                    if ($cell % 4 == 0) {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'textfield',
                            '#id' => $id_cell,
                            '#name' => $id_cell,
                            '#attributes' => [
                                'class' => ['quarter'],
                                'readonly' => 'readonly',
                            ],
//                            '#disabled' => TRUE,
                            '#value' => $value,
                        ];
                    } else {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'textfield',
                            '#id' => $id_cell,
                            '#name' => $id_cell,
                            '#value' => $value,
                            '#attributes' => [
                                'class' => ['table-head'],
//                                'onchange' => "Sum(this)",
                            ],
                            '#ajax' => [
                                'callback' => '::calc',
                                'disable-refocus' => TRUE,
                                'event' => 'change',
                            ],
                        ];
                    }
                }


                $id_ytd = $j . '_' . $row . '_' . $cell;
                if (isset($_POST[$id_ytd])) {

                    $value = $_POST[$id_ytd];
                } else {
                    $value = '';
                }

                $form[$table][$row]['YTD'] = [
                    '#type' => 'textfield',
                    '#id' => $id_ytd,
                    '#name' => $id_ytd,
                    '#value' => $value,
                    '#attributes' => [
                        'class' => ['quarter'],
                        'readonly' => 'readonly',
                    ],
//                    '#disabled' => TRUE,
                ];
            }

        }

        $form['actions']['add_table'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add table'),
            '#submit' => ['::addTable'],
        ];

        $form['actions']['send'] = [
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
        ];

        return $form;
    }


//     ф-я валидации
//
//    public function validateForm(array &$form, FormStateInterface $form_state)
//    {
//        if (strlen($form_state->getValue('name')) < 5) {
//            $form_state->setErrorByName('name', $this->t('Name is too short.'));
//        }
//    }

    public function calc(array &$form, FormStateInterface $form_state)
    {

        $id_cell = explode('_', $_POST['_triggering_element_name']);

        $pattern = $id_cell[0] . '_' . $id_cell[1] . '_';

        $id_cell_num = $id_cell[2];

        $ytd = '#' . $id_cell[0] . '_' . $id_cell[1] . '_17';

        if (in_array($id_cell_num, [1, 5, 9, 13])) {
            $quarter = $_POST[$pattern . $id_cell_num++] + $_POST[$pattern . ($id_cell_num++)] + $_POST[$pattern . ($id_cell_num++)];
        } elseif (in_array($id_cell_num, [2, 6, 10, 14])) {
            $quarter = $_POST[$pattern . ($id_cell_num - 1)] + $_POST[$pattern . ($id_cell_num++)] + $_POST[$pattern . ($id_cell_num++)];
        } else {
            $quarter = $_POST[$pattern . ($id_cell_num - 1)] + $_POST[$pattern . ($id_cell_num - 2)] + $_POST[$pattern . ($id_cell_num++)];
        }

        $quarter = round(($quarter + 1) / 3, 2);


        $id_quarter = '#' . $pattern . $id_cell_num;

        if ($id_cell_num = 4) {
            $ytd_sum = $quarter + $_POST[$pattern . '8'] + $_POST[$pattern . '12'] + $_POST[$pattern . '16'];
        } elseif
        ($id_cell_num = 8) {
            $ytd_sum = $_POST[$pattern . '4'] + $quarter + $_POST[$pattern . '12'] + $_POST[$pattern . '16'];
        } elseif
        ($id_cell_num = 12) {
            $ytd_sum = $_POST[$pattern . '4'] + $_POST[$pattern . '8'] + $quarter + $_POST[$pattern . '16'];
        } elseif
        ($id_cell_num = 16) {
            $ytd_sum = $_POST[$pattern . '4'] + $_POST[$pattern . '8'] + $_POST[$pattern . '12'] + $quarter;
        }

        $ytd_sum = round(($ytd_sum + 1) / 4, 2);

        $sum = new AjaxResponse();
        $sum->addCommand(new InvokeCommand($id_quarter, 'val', [$quarter]));
        $sum->addCommand(new InvokeCommand($ytd, 'val', [$ytd_sum]));

        return $sum;
    }


    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Перевіряємо чи була відправлена форма
        if ($_POST['op'] == 'Submit') {

            //Отримуємо кількість таблиць
            $table_count = $form_state->get('table_number');

            //Отримуємо кількість рядків в таблицях
            $row_number = $form_state->get('row_number');

            for ($table_num = 1; $table_num <= $table_count; $table_num++) {
                $row_count = $row_number['table_' . $table_num];
                for ($row_num = 1; $row_num <= $row_count; $row_num++) {
                    for ($i = 0; $i <= 4; $i++) {
                        $quarter1 = $table_num . '_' . $row_num . '_' . 4;
                        $quarter2 = $table_num . '_' . $row_num . '_' . 8;
                        $quarter3 = $table_num . '_' . $row_num . '_' . 12;
                        $quarter4 = $table_num . '_' . $row_num . '_' . 16;
                        if ($_POST[$quarter1] != ' ' && $_POST[$quarter2] == ' ' && $_POST[$quarter3] == ' ' && $_POST[$quarter4] == ' ') {

                            drupal_set_message($this->t('Valid! Q1'));

                        } else {

//                            drupal_set_message($this->t('Invalid!'));
                            $form_state->setErrorByName('name', $this->t('Invalid'));

                        }
                    }

                }
            }

            //Отримуємо кількість таблиць


//                if ('' != $all_data[$i][1][1] & $all_data[1][1][2]) {
//                    drupal_set_message($this->t('Valid!'));
//                } else {
//                    drupal_set_message($this->t('Invalid!'));
//                }


        }

//            $form_state->setErrorByName('name', $this->t('Name is too short.'));


        $form_state->disableRedirect();
    }

}
