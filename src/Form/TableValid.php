<?php

namespace Drupal\table_valid\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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
//        $table = $form_state->getTriggeringElement();
//        $table = $table['#post'];
        $table = trim(substr($_POST['op'], 8));

        $row_number = $form_state->get('row_number');
        $row_number[$table]++;

        $form_state->set('row_number', $row_number);
        $form_state->setRebuild();

        dump($_POST);
        dump($form_state);
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
                '#value' => $this->t('Add Year             ' . $table),
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
//            for ($row = 1; $row <= $row_number[$table]; $row++) {
            for ($row = $row_number[$table]; $row > 0; $row--) {

                $year = date('Y') + 1;

                $id_row = $id_row - 1;


                $form[$table][$row]['Year'] = [
                    '#type' => 'html_tag',
                    '#tag' => 'strong',
                    '#value' => $year - $row,
                ];

                $table_cell_name = $this->table_head_name();

                for ($cell = 1; $cell <= count($table_cell_name); $cell++) {

//                    $id_cell = $table . '[' . ($id_row). '][' . $cell . ']';


                    $id_cell = $j . '-' . $id_row . '-' . $cell;


                    if ($cell % 4 == 0) {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'number',
                            '#attributes' => ['class' => ['quarter'],],
                            '#disabled' => TRUE,
//                            '#id' => $id_cell,
//                            '#name' => $id_cell,
                        ];
                    } else {
                        $form[$table][$row][$table_cell_name[$cell]] = [
                            '#type' => 'number',
                            '#attributes' => ['class' => ['table-head']],
                            '#values' => [$table => [$row_number]],
//                            '#id' => $id_cell,
//                            '#name' => $id_cell,
                        ];
                    }

//                    https://zamula.uacoders.com/ru/blog/mnogoshagovye-multistep-formy-na-ajax-v-drupal-7
//
//                    $default_value = empty($form_state['values']['step2']['module']) ? '' : $form_state['values']['step2']['module'];
//
//                    if (isset($form_state->getValue(['values'][$table][$row]))) {
//                        dump($form_state['values'][$table][$row]);
//                        $form['step1']['age']['#default_value'] = $form_state['values']['step1']['age'];
//                    }


                }

                $form[$table][$row]['YTD'] = [
                    '#type' => 'number',
                    '#attributes' => ['class' => ['quarter']],
                    '#disabled' => TRUE,
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
            '#value' => $this->t('Send'),
        ];

        return $form;

    }



    // ф-я валидации
    /*
      public function validateForm(array &$form, FormStateInterface $form_state) {

        if (strlen($form_state->getValue('name')) < 5) {
          $form_state->setErrorByName('name', $this->t('Name is too short.'));
        }
      }*/


// действия по сабмиту
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message($this->t('Thank you @name, your phone number is @number', [
            '@name' => $form_state->getValue('name'),
            '@number' => $form_state->getValue('phone_number'),
        ]));
    }

}