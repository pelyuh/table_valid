<?php

namespace Drupal\table_valid\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;

class TableValid extends FormBase {

  /**
   * @return string
   *  The unique string identifying the form.
   */
  public function getFormId() {
    return 'valid_form';
  }

  /**
   * @return array
   */
  private function table_head_name() {
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

  /**
   * Method add table.
   */
  public function addTable(array &$form, FormStateInterface $form_state) {
    $table_number = $form_state->get('table_number');
    $table_number++;

    $row_number = $form_state->get('row_number');
    $row_number['table_' . $table_number] = 1;

    $form_state->set('row_number', $row_number);
    $form_state->set('table_number', $table_number);

    $form_state->setRebuild();
  }

  /**
   * Method add row.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $table = $triggering_element['#post'];

    $row_number = $form_state->get('row_number');
    $row_number[$table]++;

    $form_state->set('row_number', $row_number);

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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

      // Button add "Year".
      $form['add_row_' . $table] = [
        '#type' => 'submit',
        '#value' => $this->t('Add Year       ' . $table),
        '#submit' => ['::addRow'],
        '#post' => $table,
        '#attributes' => ['class' => ['button-add-row']],
      ];

      // Render header for table.
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

      // Adds rows.
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
          }
          else {
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
              '#value' => $value,
            ];
          }
          else {
            $form[$table][$row][$table_cell_name[$cell]] = [
              '#type' => 'textfield',
              '#id' => $id_cell,
              '#name' => $id_cell,
              '#value' => $value,
              '#attributes' => [
                'class' => ['table-head'],
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
        }
        else {
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

  public function calc() {

    $id_cell = explode('_', $_POST['_triggering_element_name']);

    $pattern = $id_cell[0] . '_' . $id_cell[1] . '_';

    $id_cell_num = $id_cell[2];

    $ytd = '#' . $id_cell[0] . '_' . $id_cell[1] . '_17';

    if (in_array($id_cell_num, [1, 5, 9, 13])) {
      $quarter = $_POST[$pattern . $id_cell_num++] + $_POST[$pattern . ($id_cell_num++)] + $_POST[$pattern . ($id_cell_num++)];
    }
    elseif (in_array($id_cell_num, [2, 6, 10, 14])) {
      $quarter = $_POST[$pattern . ($id_cell_num - 1)] + $_POST[$pattern . ($id_cell_num++)] + $_POST[$pattern . ($id_cell_num++)];
    }
    else {
      $quarter = $_POST[$pattern . ($id_cell_num - 1)] + $_POST[$pattern . ($id_cell_num - 2)] + $_POST[$pattern . ($id_cell_num++)];
    }

    if ($quarter != 0 || $quarter != ''){
      $quarter = round(($quarter + 1) / 3, 2);
    }


    $id_quarter = '#' . $pattern . $id_cell_num;

    if ($id_cell_num = 4) {
      $ytd_sum = $quarter + $_POST[$pattern . '8'] + $_POST[$pattern . '12'] + $_POST[$pattern . '16'];
    }
    elseif
    ($id_cell_num = 8) {
      $ytd_sum = $_POST[$pattern . '4'] + $quarter + $_POST[$pattern . '12'] + $_POST[$pattern . '16'];
    }
    elseif
    ($id_cell_num = 12) {
      $ytd_sum = $_POST[$pattern . '4'] + $_POST[$pattern . '8'] + $quarter + $_POST[$pattern . '16'];
    }
    elseif
    ($id_cell_num = 16) {
      $ytd_sum = $_POST[$pattern . '4'] + $_POST[$pattern . '8'] + $_POST[$pattern . '12'] + $quarter;
    }


      $ytd_sum = round(($ytd_sum + 1) / 4, 2);


    $sum = new AjaxResponse();
    $sum->addCommand(new InvokeCommand($id_quarter, 'val', [$quarter]));
    $sum->addCommand(new InvokeCommand($ytd, 'val', [$ytd_sum]));

    return $sum;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($_POST['op'] == 'Submit') {

      $error_list = [];


      //Отримуємо кількість таблиць
      $table_count = $form_state->get('table_number');

      //Отримуємо кількість рядків в таблицях
      $row_number = $form_state->get('row_number');

      $row_count = $row_number['table_1'];

      // Валідація в2

      if ($row_count == 1) {

        $cell_index_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

        for ($t = 0; $t < 12; $t++) {

          $first_cell_id = 1 . '_' . $row_count . '_' . array_shift($cell_index_1);

          // Знаходимо першу не пусту клітинку
          if ($_POST[$first_cell_id] != '') {

            $first_cell = $first_cell_id;
            $first_cell_id = explode('_', $first_cell);
            break;
          }
        }
        // Визначаємо ID наступної клітинки
        $cell_not_empty = $first_cell_id[2] + 1;

        if ($cell_not_empty % 4 == 0) {

          $cell_not_empty++;
        }

        $cell_not_empty_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

        // Перевіряємо чи наступна клітинка не пуста
        if ($_POST[$cell_not_empty_id] != '') {

          $cell_not_empty++;

          // Перевіряємо чи решта клітинок не пусті
          for ($cell_not_empty; $cell_not_empty <= 15; $cell_not_empty++) {

            if ($cell_not_empty % 4 == 0) {

              $cell_not_empty++;
            }

            $cell_not_empty_next_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

            // Якщо клітинка пуста, перевіряємо чи всі наступні не пусті
            if ($_POST[$cell_not_empty_next_id] != '') {

              if ($cell_not_empty == 15) {

                break;
              }

              $error_list['Table_1_row_' . $row_count] = 1;

            }
            else {

              if ($cell_not_empty == 15) {

                break;
              }

              $cell_not_empty++;

              if ($cell_not_empty % 4 == 0) {

                $cell_not_empty++;
              }

              $cell_not_empty_next_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

              if ($_POST[$cell_not_empty_next_id] != '') {
                $error_list['Table_1_row_' . $row_count] = 0;
                break;
              }
            }
          }

        }
        else {
          // Перевіряємо чи всі насупні пусті

          $cell_not_empty++;

          for ($cell_not_empty; $cell_not_empty <= 15; $cell_not_empty++) {


            if ($cell_not_empty % 4 == 0) {

              $cell_not_empty++;
            }

            $cell_not_empty_next_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

            if ($_POST[$cell_not_empty_next_id] != '') {

              $error_list['Table_1_row_' . $row_count] = 0;
              break;

            }
            else {

              $error_list['Table_1_row_' . $row_count] = 1;
            }
          }
        }

        // Перевірка, якщо в таблиці два рядка
      }
      elseif ($row_count == 2) {

        $cell_index_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

        for ($t = 0; $t < 12; $t++) {

          $first_cell_id = 1 . '_' . $row_count . '_' . array_shift($cell_index_1);

          // Знаходимо першу не пусту клітинку
          if ($_POST[$first_cell_id] != '') {

            $first_cell = $first_cell_id;
            $first_cell_id = explode('_', $first_cell);
            break;
          }
        }

        // Визначаємо ID наступної клітинки
        $cell_not_empty = $first_cell_id[2] + 1;

        if ($cell_not_empty % 4 == 0) {

          $cell_not_empty++;
        }

        $cell_not_empty_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

        // Перевіряємо чи наступна клітинка не пуста
        if ($_POST[$cell_not_empty_id] != '') {

          $cell_not_empty++;

          // Перевіряємо чи решта клітинок не пусті
          for ($cell_not_empty; $cell_not_empty <= 15; $cell_not_empty++) {

            if ($cell_not_empty % 4 == 0) {

              $cell_not_empty++;
            }

            $cell_not_empty_next_id = 1 . '_' . $row_count . '_' . $cell_not_empty;


            if ($_POST[$cell_not_empty_next_id] == '') {

              $error_list['Table_1_row_' . $row_count] = 0;
              break;
            }
            else {

              $error_list['Table_1_row_' . $row_count] = 1;

            }
          }

        }
        else {

          $error_list['Table_1_row_' . $row_count] = 0;

        }

        // Перевірка наступного рядка

        $row_count--;

        for ($cell_index = 1; $cell_index <= 15; $cell_index++) {

          $first_cell_id = 1 . '_' . $row_count . '_' . $cell_index;

          if ($_POST[$first_cell_id] == '') {

            $cell_index++;

            for ($cell_index; $cell_index <= 15; $cell_index++) {

              if ($cell_index % 4 == 0) {

                $cell_index++;
              }

              $first_cell_id = 1 . '_' . $row_count . '_' . $cell_index;

              if ($_POST[$first_cell_id] != '') {
                $error_list['Table_1_row_' . $row_count] = 0;
                break 2;
              }

            }
          }
        }
      }
      elseif ($row_count > 2) {

        // Перевірка першого ряжка таблиці
        $cell_index_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

        for ($t = 0; $t < 12; $t++) {

          $first_cell_id = 1 . '_' . $row_count . '_' . array_shift($cell_index_1);

          // Знаходимо першу не пусту клітинку      1
          if ($_POST[$first_cell_id] != '') {

            $first_cell = $first_cell_id;
            $first_cell_id = explode('_', $first_cell);
            break;
          }
        }

        // Визначаємо ID наступної клітинки
        if ($first_cell_id[2] == 15) {

          $error_list['Table_1_row_' . $row_count] = 1;

        }
        else {
          $cell_not_empty = $first_cell_id[2] + 1;

          if ($cell_not_empty % 4 == 0) {

            $cell_not_empty++;
          }

          $cell_not_empty_id = 1 . '_' . $row_count . '_' . $cell_not_empty;

          // Перевіряємо чи наступна клітинка не пуста
          if ($_POST[$cell_not_empty_id] != '') {

            $cell_not_empty++;

            // Перевіряємо чи решта клітинок не пусті
            for ($cell_not_empty; $cell_not_empty <= 15; $cell_not_empty++) {

              if ($cell_not_empty % 4 == 0) {

                $cell_not_empty++;
              }

              $cell_not_empty_next_id = 1 . '_' . $row_count . '_' . $cell_not_empty;


              if ($_POST[$cell_not_empty_next_id] == '') {

                $error_list['Table_1_row_' . $row_count] = 0;
                break;
              }
              else {

                $error_list['Table_1_row_' . $row_count] = 1;

              }
            }

          }
          else {

            $error_list['Table_1_row_' . $row_count] = 0;

          }
        }


        // Перевірка рядків між першим та отаннім рядком
        $row_count--;

        for ($row_count; $row_count >= 2; $row_count--) {

          for ($cell_index = 1; $cell_index <= 15; $cell_index++) {

            if ($cell_index % 4 == 0) {

              $cell_index++;
            }

            $first_cell_id = 1 . '_' . $row_count . '_' . $cell_index;

            if ($_POST[$first_cell_id] == '') {
              $error_list['Table_1_row_' . $row_count] = 0;
              break 2;
            }
          }
        }


        //Перевірка останнього рядка


        for ($cell_index = 1; $cell_index <= 15; $cell_index++) {

          $first_cell_id = 1 . '_' . $row_count . '_' . $cell_index;

          if ($_POST[$first_cell_id] == '') {

            $cell_index++;

            for ($cell_index; $cell_index <= 15; $cell_index++) {

              if ($cell_index % 4 == 0) {

                $cell_index++;
              }

              $first_cell_id = 1 . '_' . $row_count . '_' . $cell_index;

              if ($_POST[$first_cell_id] != '') {
                $error_list['Table_1_row_' . $row_count] = 0;
                break 2;
              }

            }
          }
        }
      }


      //             Валідація таблиць
      //             Вибираємо значення таблиці №1

      $row_number = $form_state->get('row_number');
      if ($table_count > 1) {


        $row_count_validate = $row_number['table_1'];
        $pattern_table = [];

        for ($row_count_validate; $row_count_validate >= 1; $row_count_validate--) {
          $cell_table_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

          for ($t = 1; $t <= 12; $t++) {

            $number_cell = 1 . '_' . $row_count_validate . '_' . array_shift($cell_table_1);
            if ($_POST[$number_cell] != '') {
              $pattern_table[$number_cell] = $number_cell;

            }
          }
        }

        // Вибираємо значення наступних таблиць

        $next_table = $table_count;

        for ($next_table; $next_table > 1; $next_table--) {
          $next_table_number = 'table_' . $next_table;
          $next_row_count_validate = $row_number[$next_table_number];

          $next_table_cell = [];


          for ($next_row_count_validate; $next_row_count_validate >= 1; $next_row_count_validate--) {
            $cell_table_1 = [1, 2, 3, 5, 6, 7, 9, 10, 11, 13, 14, 15];

            for ($t = 1; $t <= 12; $t++) {

              $number_cell = $next_table . '_' . $next_row_count_validate . '_' . array_shift($cell_table_1);
              if ($_POST[$number_cell] != '') {

                $new_number_cell = explode('_', $number_cell);
                $new_number_table = $new_number_cell[0];
                $new_number_cell[0] = $new_number_table - $next_table + 1;
                $cell_index = $new_number_cell[0] . '_' . $new_number_cell[1] . '_' . $new_number_cell[2];

                $next_table_cell[$cell_index] = $cell_index;
              }
            }
          }

          if (count($pattern_table) < count($next_table_cell)) {
            $result = array_diff_key($next_table_cell, $pattern_table);
          }
          else {
            $result = array_diff_key($pattern_table, $next_table_cell);
          }

          if ($result) {
            $error_list['table_valid_' . $next_table . '_row_' . $next_row_count_validate] = 0;
            break;
          }
          else {
            $error_list['table_valid_' . $next_table . '_row_' . $next_row_count_validate] = 1;
          }

        }

      }

      if (in_array(0, $error_list)) {
        $form_state->set('valid_result', FALSE);
      }
      else {
        $form_state->set('valid_result', TRUE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Перевіряємо чи була відправлена форма

    if ($form_state->get('valid_result')) {
      drupal_set_message($this->t('Valid!'));
    }
    else {
      drupal_set_message($this->t('Invalid!'), 'error');
    }
    $form_state->setRebuild();
  }
}
