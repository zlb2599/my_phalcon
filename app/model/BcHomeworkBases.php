<?php

class BcHomeworkBases extends \Phalcon\Mvc\Model
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
    public $lesson_id;

    /**
     *
     * @var string
     */
    public $goods_id;

    /**
     *
     * @var string
     */
    public $content;

    /**
     *
     * @var integer
     */
    public $count_student;

    /**
     *
     * @var integer
     */
    public $count_submit;

    /**
     *
     * @var integer
     */
    public $count_comment;

    /**
     *
     * @var string
     */
    public $deadline;

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

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return array(
            'id' => 'id', 
            'merchant_id' => 'merchant_id', 
            'lesson_id' => 'lesson_id', 
            'goods_id' => 'goods_id', 
            'content' => 'content', 
            'count_student' => 'count_student', 
            'count_submit' => 'count_submit', 
            'count_comment' => 'count_comment', 
            'deadline' => 'deadline', 
            'creator_id' => 'creator_id', 
            'created' => 'created', 
            'modifier_id' => 'modifier_id', 
            'modified' => 'modified', 
            'is_usable' => 'is_usable', 
            'is_delete' => 'is_delete'
        );
    }

}
