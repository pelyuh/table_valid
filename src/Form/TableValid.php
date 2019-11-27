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
//                '#ajax' => [
//                    'callback' => '::ajaxUpdateForm',
//                ],
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

            $id_row = $row_number[$table] + 1;

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

                    if ($cell % 4 == 0) {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'textfield',
                            '#id' => $id_cell,
                            '#attributes' => ['class' => ['quarter'],],
                            '#disabled' => TRUE,
//                            '#default_value' => '',
//                            '#ajax' => [
//                                'callback' => '::ajaxUpdateForm',
//                            ],


                        ];
                    } else {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'textfield',
                            '#id' => $id_cell,
                            '#attributes' => [
                                'class' => ['table-head'],
                                'onchange' => "Sum(this)",
                            ],
//                            '#ajax' => [
//                                'callback' => '::myAjaxCalc',
//                                'event' => 'change',
//                            ],
                        ];
                    }
                }
                $id_ytd = $j . '_' . $row . '_' . $cell;

                $form[$table][$row]['YTD'] = [
                    '#type' => 'number',
                    '#id' => $id_ytd,
                    '#attributes' => ['class' => ['quarter']],
                    '#disabled' => TRUE,
                ];
            }

        }

        $form['actions']['add_table'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add table'),
//            '#submit' => ['::addTable'],
            '#submit' => ['::myAjaxCalc'],
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


// действия по сабмиту
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Перевіряємо чи була відправлена форма
        if ($_POST['op'] == 'Submit') {

            //Отримуємо кількість таблиць
            $table_number = $form_state->get('table_number');

            $cell_valid_number = [1, 2, 3, 5, 6, 7, 9, 10, 11];

            // Записуємо таблиці із дамини в масив
            for ($i = 1; $i <= $table_number; $i++) {
                $table_name = 'table_' . $i;

                $table_index = substr($table_name, 6);

                $all_tables[$table_index] = $form_state->getValue($table_name);

//                for ($id_row = 1; $id_row < )
                if ('' != $all_tables[$i][1][1] & $all_tables[1][1][2]) {
                    drupal_set_message($this->t('Valid!'));
                } else {
                    drupal_set_message($this->t('Invalid!'));
                }


            }
            dump($all_tables);

//            $form_state->setErrorByName('name', $this->t('Name is too short.'));
        }

        $form_state->disableRedirect();
    }

}
