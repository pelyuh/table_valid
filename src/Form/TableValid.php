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

    public function calc()
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

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if ($_POST['op'] == 'Submit') {

            $error_list = [];


            //Отримуємо кількість таблиць
            $table_count = $form_state->get('table_number');

            //Отримуємо кількість рядків в таблицях
            $row_number = $form_state->get('row_number');

            $row_count = $row_number['table_1'];

            // Валідація в2

            for ($row_count; $row_count > 0; $row_count--) {

                $cell_index_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

                // Пошук та валідація в першому рядку таблиці клітинки із значеннями
//                if (empty($cell_id)) {

                for ($t = 0; $t < 12; $t++) {

                    $first_cell_id = 1 . '_' . $row_count . '_' . array_shift($cell_index_1);

                    // Знахлдим першу не песту клітинку
                    if ($_POST[$first_cell_id] != '') {

                        $first_cell = $first_cell_id;
                        $first_cell_id = explode('_', $first_cell);


                        if ($first_cell_id[2] == 1) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 2;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 3;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 2) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 3;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 5;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 3) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 5;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 6;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 5) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 6;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 7;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 6) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 7;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 9;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 7) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 9;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 10;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 9) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 10;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 11;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 10) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 10;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 13;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 11) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 13;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 14;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 13) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 14;
                            $next_cell_id_2 = 1 . '_' . $row_count . '_' . 15;

                            if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                $error_list['line'] = 0;
                            }
                        } elseif ($first_cell_id[2] == 14) {

                            $next_cell_id_1 = 1 . '_' . $row_count . '_' . 15;
                            $next_row_count = $row_count - 1;
                            $next_cell_id_2 = 1 . '_' . $next_row_count . '_' . 1;

                            if (!(empty($_POST[$next_cell_id_2]))) {
                                if ($_POST[$next_cell_id_1] == '' && $_POST[$next_cell_id_2] != '') {
                                    $error_list['line'] = 0;
                                }
                            }
                        }

                    }

                }
            }


            // Валідація комірок
//            for ($row_count; $row_count > 0; $row_count--) {
//
//                $cell_index_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];
//
//                // Пошук та валідація в першому рядку таблиці клітинки із значеннями
//                if (empty($cell_id)) {
//
//                    for ($t = 0; $t < 12; $t++) {
//
//                        $first_cell_id = 1 . '_' . $row_count . '_' . array_shift($cell_index_1);
//                        if ($_POST[$first_cell_id] != '') {
//
//                            $first_cell_id = explode('_', $first_cell_id);
//
//                            for ($i = $first_cell_id[2]; $i < 16; $i++) {
//                                if (!($i % 4 == 0)) {
//                                    $first_cell_id = 1 . '_' . $row_count . '_' . $i;
//                                    if ($_POST[$first_cell_id] != 0) {
//                                        $index_is_cell = explode('_', $first_cell_id);
//                                        if (in_array($index_is_cell[2], [5, 6, 7])) {
//
//                                            $cell_array[] = $index_is_cell[2] - 1;
//
//                                        } elseif (in_array($index_is_cell[2], [9, 10, 11])) {
//
//                                            $cell_array[] = (int)$index_is_cell[2] - 2;
//
//                                        } elseif (in_array($index_is_cell[2], [13, 14, 15])) {
//
//                                            $cell_array[] = (int)$index_is_cell[2] - 3;
//
//                                        } else {
//
//                                            $cell_array[] = (int)$index_is_cell[2];
//
//                                        }
//                                    }
//                                }
//                            }
//
//                            $cell_array_count = count($cell_array);
//                            if ($cell_array_count != 1) {
//
//                                $first_line_log = [];
//                                $iterator = $cell_array[0];
//
//                                for ($i = 0; $i < count($cell_array); $i++) {
//
//                                    if ($cell_array[$i] == $iterator) {
//                                        $first_line_log[] = 1;
//                                    } else {
//                                        $first_line_log[] = 0;
//                                    }
//                                    $iterator++;
//                                }
//
//                                if (in_array(0, $first_line_log)) {
//                                    $error_list['first_line'] = 0;
//
//                                } else {
//                                    $error_list['first_line'] = 1;
//                                }
//                            } else {
//                                $error_list['first_line'] = 1;
//                            }
//                        }
//                        break;
//                    }
//                }
//
            // Валідація таблиць

            // Валідація таблиць
            // Вибираємо значення таблиці №1
            $table_count = $form_state->get('table_number');
            $row_number = $form_state->get('row_number');
            if ($table_count > 1) {



                $row_count_validate = $row_number['table_1'];
                $pattern_table = [];

                for ($f = 0; $f < $row_count_validate; $f++) {
                    $cell_table_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

                    for ($t = 1; $t <= 12; $t++) {

                        $number_cell = 1 . '_' . $row_count_validate . '_' . array_shift($cell_table_1);
                        if ($_POST[$number_cell] != '') {
                            $pattern_table[$number_cell] = $number_cell;

                        }
                    }

                    // Вибираємо значення наступних таблиці №1

                    $next_table = $table_count;

                    for ($next_table; $next_table > 1; $next_table--) {
                        $next_table_number = 'table_' . $next_table;
                        $next_row_count_validate = $row_number[$next_table_number];

                        ${'next_table_array' . $next_table} = [];
                        $fix_next_table = $next_table - 1;

                        for ($f = 0; $f < $next_row_count_validate; $f++) {
                            $cell_table_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

                            for ($t = 1; $t <= 12; $t++) {
                                $cell_number = array_shift($cell_table_1);
                                $number_cell = $next_table . '_' . $next_row_count_validate . '_' . $cell_number;
                                if ($_POST[$number_cell] != '') {
                                    $cell_index = $next_table - $fix_next_table . '_' . $next_row_count_validate . '_' . $cell_number;

                                    ${'next_table_array' . $next_table}[$cell_index] = $cell_index;

                                }
                            }

                            if (count($pattern_table) < count(${'next_table_array' . $next_table})) {
                                $result = array_diff_key(${'next_table_array' . $next_table}, $pattern_table);
                            } else {
                                $result = array_diff_key($pattern_table, ${'next_table_array' . $next_table});
                            }

                            if ($result) {
                                $error_list['table_valid_' . $next_table . '_row_' . $f] = 0;
                            } else {
                                $error_list['table_valid_' . $next_table . '_row_' . $f] = 1;
                            }
                        }
                    }
                }
            }

            if (in_array(0, $error_list)) {
                $form_state->set('valid_result', FALSE);
            } else {
                $form_state->set('valid_result', TRUE);
            }
        }
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        // Перевіряємо чи була відправлена форма

        if ($form_state->get('valid_result')) {
            drupal_set_message($this->t('Valid!'));
        } else {
            drupal_set_message($this->t('Invalid!'), 'error');
        }
        $form_state->setRebuild();
    }
}
