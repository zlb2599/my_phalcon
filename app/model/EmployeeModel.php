<?php
/**
 * 员工模型
 * PHP versions 5.6
 * @copyright  Copyright 2012-2016, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link       http://api.baonahao.com api(tm) Project
 * @package    api
 * @subpackage api/app
 * @date       2016-05-20 18:05
 * @author     liuhefei <biguangfu@xiaohe.com>
 */

class EmployeeModel extends AppModel
{
    public function initialize()
    {
        $this->setSource('mc_employees');                      //模型对应的表名
        $this->setReadConnectionService('member_slave');     //从库
        $this->setWriteConnectionService('member_master');   //主库
    }

    /*
    * 获取会员商家关系信息(员工数据)
    * @param string $member_id
    * @param string $merchant_id
    * @return array
    * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
    * @date 2017-11-28 10:54
    */
    public function getEmployeeInfo($member_id, $merchant_id)
    {
        //查询会员信息是否存在
        $conditions = [
            "merchant_id = :merchant_id:",
            "member_id = :member_id:",
            "is_delete = :is_delete:",
        ];

        $bind = [
            'merchant_id' => $merchant_id,
            'member_id'   => $member_id,
            'is_delete'   => 2,
        ];

        $employee_result = $this->findFirst([
            'columns'    => 'id,type,role_type,invite_status,modified',
            'conditions' => implode(' AND ', $conditions),
            'bind'       => $bind,
        ]);

        return empty($employee_result)?[]:$employee_result->toArray();
    }

    /*
    * 查询员工信息
    * @param string $employee_id 员工ID
    * @return array
    * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
    * @date 2017-11-28 10:54
    */
    public function getEmployee($employee_id)
    {
        $sqlJoin = new SqlJoin();

        $db_security_code = getSecurityCode();
        $condition_str    = " WHERE employee.is_usable=1 AND employee.is_delete=2 AND employee.data_enter_type IN (1,2) AND employee.id='{$employee_id}' ";
        //字段
        $field_str = " employee.id AS employee_id,employee.type,employee.invite_status,employee.created,employee.modified, employee.invite_time, employee.merchant_id,employee.employee_photo,";
        $field_str .= " member.id AS member_id,DECODE(member.phone, '{$db_security_code}') AS phone, member.realname, member.nickname ,member.password,member.sex ";

        //SQL
        $sql = " SELECT {$field_str} FROM mc_employees AS employee ";
        $sql .= " LEFT JOIN mc_members AS member ON employee.member_id = member.id ";
        $sql .= $condition_str;

        //数据
        $result = $sqlJoin->query($this, $sql, 'find');

        //机构名称
        if (!empty($result) && !empty($merchant_id = $result['merchant_id'])) {
            $model                   = new MerchantModel();
            $result['merchant_name'] = $model->getMerchantName($merchant_id);
        }

        return $result;
    }


    /**
     * 描述：根据ID查询
     * @return array
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * @date 2017-11-28 10:54
     */
    public function findById($id)
    {
        $where['conditions'] = "id='{$id}'";
        $result              = $this->findFirst($where);

        return empty($result)?array():$result->toArray();
    }

    /**
     * 描述：修改员工基本信息
     * @return true
     * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
     * @date 2017-11-28 10:54
     */
    public function updateEmployee($data)
    {
        $modifier_id = getArrVal($data, 'operator_id');
        $modified    = date("Y-m-d H:i:s");
        $employee_id = getArrVal($data, 'employee_id');
        $info        = getArrVal($data, 'employee');
        $sqlJoin     = new SqlJoin();

        $obj = $this->findFirst(['conditions' => "id='{$employee_id}'"]);
        if (empty($obj)) {
            DLOG("updateEmployee:Employee is empty", 'ERROR', "sql-error.log");

            return false;
        }
        if (!empty($info) && is_array($info)) {
            $sql = "UPDATE member_center.`mc_employees`  SET ";
            // 判断是否是课程顾问
            if ($info['is_assistant'] == 1) {
                $sql .= "type = '{$info['type']},4',";
                unset($info['type']);
            }
            unset($info['is_assistant']);
            foreach ($info as $k => $v) {
                $sql .= "`{$k}` = '{$v}',";
            }
            $sql .= " `modifier_id` = '{$modifier_id}', `modified` = '{$modified}' WHERE `id` = '{$employee_id}'";
            DLOG($sql, 4, 'asd.sql');
            try {
                $this->getWriteConnection()->execute($sql);

            }
            catch (\Exception $ex) {
                if ($ex->getMessage() != 'SQLSTATE[HY000]: General error') {
                    DLOG("updateEmployee:".$ex->getMessage(), 'ERROR', "sql-error.log");
                }
            }

            return true;
        }
    }

    //    /**
    //     * 获取员工岗位名称
    //     * @param string $employee_id 员工id
    //     * @return array
    //     * @author liuxing <liuxing@xiaohe.com>
    //     * @date 2017-11-28 10:54
    //     */
    //    public function getEmployeePostName($employee_id)
    //    {
    //        $sql = "SELECT p.name FROM mc_employee_role as e LEFT JOIN mc_platform_roles as p ON e.role_id = p.id WHERE e.employee_id = '{$employee_id}'";
    //        $result = $this->setSource('')
    //    }

    public function getEmployeeList($condition, $page, $page_size)
    {
        $merchant_id      = getArrVal($condition, 'merchant_id');
        $info             = getArrVal($condition, 'info');
        $job_type         = getArrVal($condition, 'job_type');
        $job_status       = getArrVal($condition, 'job_status');
        $department_name  = getArrVal($condition, 'department_name');
        $is_teacher       = getArrVal($condition, 'is_teacher');
        $invite_status    = getArrVal($condition, 'invite_status');
        $department_id    = getArrVal($condition, 'department_id');
        $realname_initial = getArrVal($condition, 'realname_initial');

        $db_security_code = getConfig('db_security_code');

        $sql = "SELECT e.id,e.member_id, e.id AS employee_id,e.invite_status,e.type as employee_type,e.job_type as employee_job_type,e.job_status,";
        $sql .= " DECODE(m.phone, '{$db_security_code}') AS phone,m.realname,m.nickname,m.realname_initial,";
        $sql .= " e.merchant_department_id,d.department_name";
        $sql .= " FROM ".$this->getSource()." e";
        $sql .= " JOIN mc_members m ON e.member_id=m.id";
        $sql .= " JOIN mc_merchant_departments d ON e.merchant_department_id=d.id";
        $sql .= " WHERE e.is_usable=1 AND e.is_delete=2 AND m.is_usable=1 AND m.is_delete=2";
        $sql .= " AND e.merchant_id='{$merchant_id}' AND find_in_set(1,e.type)";
        if ($info) {
            $sql .= " AND (m.realname LIKE '%{$info}%' OR (DECODE(m.phone,'{$db_security_code}') LIKE '%{$info}%'))";
        }
        if ($job_type) {
            $sql .= " AND e.job_type = {$job_type}";
        }
        if ($job_status) {
            $sql .= " AND e.job_status={$job_status}";
        }
        if ($department_name) {
            $sql .= " AND (d.department_name LIKE '%{$department_name}%')";
        }
        if ($invite_status) {
            $sql .= " AND e.invite_status ={$invite_status}";
        }
        if ($is_teacher) {
            if ($is_teacher == 1) {
                $sql .= "AND find_in_set(2, employee.type) ";
            } else {
                $sql .= "AND employee.type NOT LIKE '%2%' ";
            }
        }
        if ($department_id) {
            if (is_array($department_id)) {
                foreach ($department_id as &$value) {
                    $value = "'{$value}'";
                }
                $sql .= " AND e.merchant_department_id in (".join(',', $department_id).")";
            } else {
                $sql .= " AND e.merchant_department_id !=''";
            }
        }
        if ($realname_initial) {
            foreach ($realname_initial as &$value) {
                $value = "'{$value}'";
            }
            $sql .= " AND m.realname_initial IN (".join(',', $realname_initial).")";
        }
        $offset = ($page - 1) * $page_size;
        $sql    .= " LIMIT {$offset},{$page_size}";

        $result = $this->getReadConnection()->query($sql);
        $result->setFetchMode(Phalcon\Db::FETCH_ASSOC);

        return $result->fetchAll();


    }

    public function getEmployeeCount($condition)
    {
        $merchant_id      = getArrVal($condition, 'merchant_id');
        $info             = getArrVal($condition, 'info');
        $job_type         = getArrVal($condition, 'job_type');
        $job_status       = getArrVal($condition, 'job_status');
        $department_name  = getArrVal($condition, 'department_name');
        $is_teacher       = getArrVal($condition, 'is_teacher');
        $invite_status    = getArrVal($condition, 'invite_status');
        $department_id    = getArrVal($condition, 'department_id');
        $realname_initial = getArrVal($condition, 'realname_initial');

        $db_security_code = getConfig('db_security_code');

        $sql = "SELECT count(1) as num";
        $sql .= " FROM ".$this->getSource()." e";
        $sql .= " JOIN mc_members m ON e.member_id=m.id";
        $sql .= " JOIN mc_merchant_departments d ON e.merchant_department_id=d.id";
        $sql .= " WHERE e.is_usable=1 AND e.is_delete=2 AND m.is_usable=1 AND m.is_delete=2";
        $sql .= " AND e.merchant_id='{$merchant_id}' AND find_in_set(1,e.type)";
        if ($info) {
            $sql .= " AND (m.realname LIKE '%{$info}%' OR (DECODE(m.phone,'{$db_security_code}') LIKE '%{$info}%'))";
        }
        if ($job_type) {
            $sql .= " AND e.job_type = {$job_type}";
        }
        if ($job_status) {
            $sql .= " AND e.job_status={$job_status}";
        }
        if ($department_name) {
            $sql .= " AND (d.department_name LIKE '%{$department_name}%')";
        }
        if ($invite_status) {
            $sql .= " AND e.invite_status ={$invite_status}";
        }
        if ($is_teacher) {
            if ($is_teacher == 1) {
                $sql .= "AND find_in_set(2, employee.type) ";
            } else {
                $sql .= "AND employee.type NOT LIKE '%2%' ";
            }
        }
        if ($department_id) {
            if (is_array($department_id)) {
                foreach ($department_id as &$value) {
                    $value = "'{$value}'";
                }
                $sql .= " AND e.merchant_department_id in (".join(',', $department_id).")";
            } else {
                $sql .= " AND e.merchant_department_id !=''";
            }
        }
        if ($realname_initial) {
            foreach ($realname_initial as &$value) {
                $value = "'{$value}'";
            }
            $sql .= " AND m.realname_initial IN (".join(',', $realname_initial).")";
        }
        $result = $this->getReadConnection()->query($sql);

        return $result->fetch()['num'];

    }

}