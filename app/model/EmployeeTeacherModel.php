<?php

class EmployeeTeacherModel extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    public $employee_id;

    /**
     *
     * @var string
     */
    public $campus_id;

    /**
     *
     * @var integer
     */
    public $seniority;

    /**
     *
     * @var string
     */
    public $intro;

    /**
     *
     * @var string
     */
    public $photo;

    /**
     *
     * @var string
     */
    public $grade_id;

    /**
     *
     * @var string
     */
    public $job_type;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $grade_level_ids;

    /**
     *
     * @var string
     */
    public $category_merchant_level_ids;

    /**
     *
     * @var string
     */
    public $one_category_id;

    /**
     *
     * @var string
     */
    public $one_category_name;

    /**
     *
     * @var string
     */
    public $two_category_id;

    /**
     *
     * @var string
     */
    public $two_category_name;

    /**
     *
     * @var string
     */
    public $three_category_id;

    /**
     *
     * @var string
     */
    public $three_category_name;

    /**
     *
     * @var string
     */
    public $label;

    /**
     *
     * @var string
     */
    public $is_assistant;

    /**
     *
     * @var string
     */
    public $idcard;

    /**
     *
     * @var string
     */
    public $subject_ids;

    /**
     *
     * @var string
     */
    public $grade_type_ids;

    /**
     *
     * @var string
     */
    public $grade_ids;

    /**
     *
     * @var string
     */
    public $data_enter_type;

    public function initialize()
    {
        $this->setSource('mc_employee_teachers');         //模型对应的表名
        $this->setReadConnectionService('member_slave');     //从库
        $this->setWriteConnectionService('member_master');   //主库
    }

}
