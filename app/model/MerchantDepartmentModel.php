<?php

class MerchantDepartmentModel extends AppModel
{

    /**
     *
     * @var string
     */
    public $id;

    /**
     *
     * @var string
     */
    public $merchant_id;

    /**
     *
     * @var string
     */
    public $platform_id;

    /**
     *
     * @var string
     */
    public $department_name;

    /**
     *
     * @var string
     */
    public $pid;

    /**
     *
     * @var integer
     */
    public $department_type;

    /**
     *
     * @var string
     */
    public $remark;

    /**
     *
     * @var string
     */
    public $creator_id;

    /**
     *
     * @var string
     */
    public $created;

    /**
     *
     * @var string
     */
    public $modifier_id;

    /**
     *
     * @var string
     */
    public $modified;

    /**
     *
     * @var integer
     */
    public $is_usable;

    /**
     *
     * @var integer
     */
    public $is_delete;

    public function initialize()
    {
        $this->setSource('mc_merchant_departments');         //模型对应的表名
        $this->setReadConnectionService('member_slave');     //从库
        $this->setWriteConnectionService('member_master');   //主库
    }
}
