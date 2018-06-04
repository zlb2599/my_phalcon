<?php
/**
 * 商品模型
 * @copyright Copyright 2012-2017, BAONAHAO Software Foundation, Inc. ( http://apibusiness.baonahao.com/ )
 * @link http://apibusiness.baonahao.com apibusiness(tm) Project
 * @author zhaodongjuan <zhaodongjuan@xiaohe.com>
 */
use Phalcon\Mvc\Model;
class GoodsModel extends AppModel
{
    public function initialize()
    {
        $this->setSource('gc_goods');     //模型对应的表名
        $this->setReadConnectionService('goods_slave');     //从库
        $this->setWriteConnectionService('goods_master');   //主库
    }

    /**
     * 获取班级信息
     * @author houguopeng <houguopeng@xiaohe.com>
     * @date 2017-11-16 15:43
     */
    public function getGoodsList($data)
    {
        //参数
        $goods_ids   = getArrVal($data, 'goods_ids');
        $merchant_id = getArrVal($data, 'merchant_id');
        $campus_ids  = getArrVal($data, 'campus_ids');
        $offset      = getArrVal($data, 'offset', 0);
        $page_size   = getArrVal($data, 'page_size', 20);

        //字段
        $field = 'otm.goods_id,goods.`name` as goods_name,goods.mall_cost,otm.open_date,';
        $field .= 'otm.end_date,otm.teacher_id,otm.campus_id';
        //sql
        $sql = "SELECT {$field} FROM `goods_center`.`gc_goods` as goods ";
        $sql .= 'LEFT JOIN goods_center.gc_goods_class_otms as otm ON goods.id=otm.goods_id ';
        //where
        $sql .= "WHERE goods.is_usable = '1' AND goods.is_delete = '2' AND goods.merchant_id='{$merchant_id}' ";
        $sql .= "AND is_shelf = '1' ";

        //基础判断条件 班级分类
        $type_ids = array2string(['518694abc1bb11e6be774439c44fd9ad', '519451b050d44582ab3e08430a00c776', '9d567138c1bb11e6be774439c44fd9ad', '519451b050d44582ab3e08430a00c775']);

        $sql .= "AND goods.end_date IS NOT NULL  AND type_id IN ({$type_ids}) ";

        if (!empty($goods_ids)) {
            $sql .= "AND goods.id IN({$goods_ids}) ";
        }
        if (!empty($campus_ids)) {
            $sql .= "AND otm.campus_id IN({$campus_ids}) ";
        }
        $sql .= "AND ((is_enforce=1 AND is_transfer='1') OR is_enforce=4)";

        $sql .= 'ORDER BY browse_number DESC ';

        $sql .= "limit {$offset},{$page_size}";

        $resault = sqlImplement($sql, 'GoodsModel');

        return $resault;
    }


    /**
     * 描述：
     * @return array
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function findById($id)
    {
        $where['conditions'] = "id='{$id}'";
        $result              = $this->findFirst($where);

        return empty($result)?array():$result->toArray();
    }

    /**
     * 查询课程
     * @access public
     * ----------------------------------------------------------
     * @param  array $data 请求数据
     * ----------------------------------------------------------
     * @return array
     * ----------------------------------------------------------
     * @author houguopeng <houguopeng@xiaohe.com>
     * ----------------------------------------------------------
     * @date 2017-01-03 21:04
     * ----------------------------------------------------------
     */
    public function getGoodsByIds($goods_ids)
    {
        $condition_str = "id IN({$goods_ids}) AND is_delete = '2' AND is_usable= '1'";
        $goods         = $this->find(array(
            'columns'    => 'id,type_id,merchant_id,name',
            'conditions' => $condition_str,
        ))->toArray();

        return $goods;
    }


}
