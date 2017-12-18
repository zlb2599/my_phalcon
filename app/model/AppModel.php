<?php

/**
 * 基类模型层
 * @copyright Copyright 2012-2017, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @author zhanglibo <zhanglibo@xiaohe.com>
 */
class AppModel extends \Phalcon\Mvc\Model
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var integer
     */
    public $is_usable;

    /**
     * @var integer
     */
    public $is_delete;


    /**
     * @var string
     */
    public $created;


    /**
     * @var string
     */
    public $modified;

    /**
     * 通过ID查询
     * @param        $id
     * @param string $field
     * @param bool   $to_array
     * @return array|\Phalcon\Mvc\Model
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getById($id, $field = '*', $to_array = true)
    {
        if (empty($id)) {
            return [];
        }
        $result = $this->FindFirst([
            'columns'    => $field,
            'conditions' => 'id=:id:',
            'bind'       => ['id' => $id],
        ]);
        if ($to_array) {
            return $result?$result->toArray():[];
        }

        return $result;
    }

    /**
     * 查询单条记录
     * @param        $condition
     * @param string $field
     * @param bool   $to_array
     * @return array|\Phalcon\Mvc\Model
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getOne($condition, $field = "*", $to_array = true)
    {
        $where  = $this->getCondition($condition);
        $result = $this->findFirst([
            'columns'    => $field,
            'conditions' => $where,
            'bind'       => $this->getBind($condition),
        ]);

        if ($to_array) {
            return $result?$result->toArray():[];
        }

        return $result;
    }

    /**
     * 查询多条记录-带分页
     * @param array  $condition where条件
     * @param string $field 字段
     * @param int    $page 页码
     * @param int    $pageSize 记录条数
     * @param null   $order 排序
     * @param bool   $to_array 是否转数组
     * @return array|\Phalcon\Mvc\Model\ResultsetInterface
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getList($condition, $field = "*", $page = 1, $pageSize = 10, $order = null, $to_array = true)
    {
        $where  = $this->getCondition($condition);
        $offset = ($page - 1) * $pageSize;

        $result = $this->find([
            'columns'    => $field,
            'conditions' => $where,
            'bind'       => $this->getBind($condition),
            'limit'      => $pageSize,
            'offset'     => $offset,
            'order'      => $order
        ]);

        if ($to_array) {
            return $result?$result->toArray():[];
        }

        return $result;
    }

    /**
     * 查询多条记录-全部
     * @param        $condition
     * @param string $field
     * @param null   $order
     * @param bool   $to_array
     * @return array|\Phalcon\Mvc\Model\ResultsetInterface
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getAll($condition, $field = "*", $order = null, $to_array = true)
    {
        $where = $this->getCondition($condition);

        $result = $this->find([
            'columns'    => $field,
            'conditions' => $where,
            'bind'       => $this->getBind($condition),
            'order'      => $order
        ]);

        if ($to_array) {
            return $result?$result->toArray():[];
        }

        return $result;
    }

    /**
     * 添加-单条记录
     * @param array $data
     * @return bool
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function add($data)
    {
        if (empty($data)) {
            return false;
        }
        $attributes = $this->getModelsMetaData()->getAttributes($this);
        if (in_array('created', $attributes)) {
            $data['created'] = date('Y-m-d H:i:s');
        }
        if (in_array('modified', $attributes)) {
            $data['modified'] = date('Y-m-d H:i:s');
        }
        if (in_array('is_usable', $attributes)) {
            $data['is_usable'] = 1;
        }
        if (in_array('is_delete', $attributes)) {
            $data['is_delete'] = 2;
        }
        $this->create($data);

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();
    }

    /**
     * 添加-多条记录
     * @param      $data
     * @param null $encode_field
     * @return mixed
     * @author zhanglibo <zhanglibo@xiaohe.com>
     * @throws Exception
     */
    public function addAll($data, $encode_field = null)
    {
        $db_security_code = getConfig('DB_SECURITY_CODE');

        $keys = array_keys(reset($data));
        $keys = array_map(function ($key)
        {
            return "`{$key}`";
        }, $keys);
        $keys = implode(',', $keys);
        $sql  = "INSERT INTO ".$this->getSource()." ({$keys}) VALUES ";
        foreach ($data as $k => $v) {
            array_walk($v, function (&$val, $key) use ($encode_field, $db_security_code)
            {
                if ($key == $encode_field) {
                    $val = "ENCODE('{$val}','{$db_security_code}')";
                } else {
                    $val = "'{$val}'";
                }
            });
            $values = implode(',', array_values($v));
            $sql    .= " ({$values}), ";
        }
        $sql    = rtrim(trim($sql), ',');
        $result = $this->getDI()->get($this->getWriteConnectionService())->execute($sql);
        if (!$result) {
            throw new \Exception('批量插入失败');
        }

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();

    }

    /**
     * 更新-通过ID
     * @param array|null $data
     * @param null       $whiteList
     * @return bool
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function updateById(array $data = null, $whiteList = null)
    {
        $this->id = getArrVal($data, 'id');
        if (count($data) > 0) {
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            if (in_array('modified', $attributes)) {
                $data['modified'] = date('Y-m-d H:i:s');
            }
            $this->skipAttributesOnUpdate(array_diff($attributes, array_keys($data)));
        }

        $this->update($data, $whiteList);

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();
    }

    /**
     * 添加、更新-通过ID
     * @param array|null $data
     * @param null       $whiteList
     * @return bool
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function saveById(array $data = null, $whiteList = null)
    {
        if (count($data) > 0) {
            $attributes = $this->getModelsMetaData()->getAttributes($this);
            if (in_array('modified', $attributes)) {
                $data['modified'] = date('Y-m-d H:i:s');
            }
            $this->skipAttributesOnUpdate(array_diff($attributes, array_keys($data)));

        }
        $this->save($data, $whiteList);

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();

    }

    /**
     * 更新
     * @param array|string $condition 条件
     * @param array        $data 更新字段
     * @param string       $encode_field encode字段
     * @return bool
     * @author zhanglibo <zhanglibo@xiaohe.com>
     * @throws Exception
     */
    public function updateRecord($condition, $data, $encode_field = null)
    {
        $db_security_code = getConfig('DB_SECURITY_CODE');

        if (count($condition) == 0 || count($data) == 0) {
            return false;
        }
        $attributes = $this->getModelsMetaData()->getAttributes($this);
        if (in_array('modified', $attributes)) {
            $data['modified'] = date('Y-m-d H:i:s');
        }
        array_walk($data, function (&$val, $key) use ($encode_field, $db_security_code)
        {
            if ($key == $encode_field) {
                $val = "`{$key}`=ENCODE('{$val}','{$db_security_code}')";
            } else {
                $val = "`{$key}`='{$val}'";
            }
        });
        $set = join(',', $data);
        if (!is_array($condition)) {
            //字符串
            $where = $condition;
        } else {
            array_walk($condition, function (&$val, $key)
            {
                $val = "`{$key}`='{$val}'";
            });
            $where = join(',', $condition);
        }
        if (empty($set) || empty($where)) {
            throw new \Exception('更新失败');
        }
        $sql = "UPDATE `{$this->getSource()}` SET {$set} WHERE {$where}";

        $result = $this->getDI()->get($this->getWriteConnectionService())->execute($sql);
        if (!$result) {
            throw new \Exception('更新失败');
        }

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();
    }

    /**
     * where 条件
     * @param $condition
     * @return string
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    protected function getCondition($condition)
    {
        if (is_string($condition)) {
            return $condition;
        }

        foreach ($condition as $k => $v) {
            if (is_array($v)) {
                unset($condition[$k]);
            }
        }
        $keys = array_keys($condition);

        $where = array_map(function ($val)
        {
            return "{$val}=:{$val}:";
        }, $keys);

        return join(' and ', $where);

    }

    /**
     * 查询-通过ID列表
     * @param        $id
     * @param string $field 字段
     * @param string $pk 主键
     * @param bool   $id_to_key 是否以Id为主键返回
     * @return array|\Phalcon\Mvc\Model\ResultsetInterface
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getListById($id, $field = "*", $pk = 'id', $id_to_key = true)
    {
        if (empty($id)) {
            return [];
        }
        $column = $field;
        if ($field != '*' && !in_array($pk, explode(',', strtolower($field)))) {
            $column = "{$field},{$pk}";
        }
        $str    = "{$pk} in ({{$pk}:array})";
        $result = $this->find([
            'columns'    => $column,
            'conditions' => $str,
            'bind'       => [$pk => $id]
        ]);
        if (empty($result)) {
            return [];
        }
        $result = $result->toArray();

        if ($id_to_key) {
            if (count(reset($result)) == 2) {
                //单字段
                return array_column($result, $field, $pk);
            }

            //多字段
            return array_column($result, null, $pk);
        }

        return $result;
    }

    /**
     * 查询-统计
     * @param $condition
     * @return mixed
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function getTotal($condition)
    {
        $where = $this->getCondition($condition);

        $result = $this->count([
            'conditions' => $where,
            'bind'       => $this->getBind($condition),
        ]);

        return $result;
    }

    /**
     * bind 是否为数组
     * @param $condition
     * @return null
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    protected function getBind($condition)
    {
        return is_string($condition)?null:$condition;
    }

    /**
     * 数据填充-添加
     * @author zhanglibo <zhanglibo@xiaohe.com>
     */
    public function beforeCreate()
    {
        $attributes = $this->getModelsMetaData()->getAttributes($this);
        if (in_array('created', $attributes)) {
            $this->created = date('Y-m-d H:i:s');
        }
        if (in_array('modified', $attributes)) {
            $this->modified = date('Y-m-d H:i:s');
        }
        if (in_array('is_usable', $attributes)) {
            $this->is_usable = 1;
        }
        if (in_array('is_delete', $attributes)) {
            $this->is_delete = 2;
        }
    }


    /**
     * 添加-有encode字段
     * @param $data
     * @param $encode_field
     * @return mixed
     * @author zhanglibo <zhanglibo@xiaohe.com>
     * @throws Exception
     */
    public function addEncode($data, $encode_field)
    {
        $db_security_code = getConfig('DB_SECURITY_CODE');

        $keys = array_keys($data);
        $keys = array_map(function ($key)
        {
            return "`{$key}`";
        }, $keys);
        //默认值
        $attributes = $this->getModelsMetaData()->getAttributes($this);
        if (in_array('created', $attributes)) {
            $data['created'] = date('Y-m-d H:i:s');
        }
        if (in_array('modified', $attributes)) {
            $data['modified'] = date('Y-m-d H:i:s');
        }
        if (in_array('is_usable', $attributes)) {
            $data['is_usable'] = 1;
        }
        if (in_array('is_delete', $attributes)) {
            $data['is_usable'] = 2;
        }
        $keys = implode(',', $keys);
        $sql  = "INSERT INTO ".$this->getSource()." ({$keys}) VALUES ";

        array_walk($data, function (&$val, $key) use ($encode_field, $db_security_code)
        {
            if ($key != $encode_field) {
                $val = "'{$val}'";
            } else {
                $val = "ENCODE('{$val}','{$db_security_code}')";
            }
        });
        $sql .= " (".implode(',', $data).") ";

        $result = $this->getDI()->get($this->getWriteConnectionService())->execute($sql);
        if (!$result) {
            throw new \Exception('添加失败');
        }

        return $this->getDI()->get($this->getWriteConnectionService())->affectedRows();
    }


}