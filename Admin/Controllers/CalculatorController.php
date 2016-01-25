<?php

namespace Modules\Employee\Admin\Controllers;

/**
 * @acesss (SALARY_SHEET)
 */
class CalculatorController extends BaseController
{

    /**
     * @param integer $id
     *
     * @Method (AJAX)
     */
    public function baseAction($id)
    {
        $form = $this->form('Calculator\\Base');
        $form->setData('user_id', $id);
        if ($this->request->is('POST')) {
            $form->handle($this->request->post);
            if (!$form->validate()) {
                return [
                    'errors' => $form->getErrors(),
                ];
            }
            if ($form->isSubmitted()) {
                $base = $form->save();
                return [
                    'base' => $base,
                ];
            }
        }
        else {
            $query = $this->request->query->all();
            $year = \Arr::get($query, 'year', date('Y'));
            $month = \Arr::get($query, 'month', date('m'));
            $data = $year.'-'.$month.'-01';
            $form->setData('date', $data);
            return $this->renderPartial('calculator/base', [
                'form' => $form->createBuilder(),
                'user_id' => $id,
                'data' => $data
            ]);
        }
    }
  
}