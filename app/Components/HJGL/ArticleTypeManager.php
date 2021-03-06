<?php

/**
 * Created by PhpStorm.
 * User: HappyQi
 * Date: 2017/9/28
 * Time: 10:30
 */

namespace App\Components\HJGL;

use App\Components\Utils;
use App\Models\HJGL\ArticleType;
use Qiniu\Auth;

class ArticleTypeManager
{
    /*
     * 根据id用or条件获取文章分类
     *
     * By yuyang
     *
     * 2018-12-17
     */
    public static function getInfoByorId($info,$level = 0){
        if(strpos($level,'0')!== false){
            $ids = array();
            foreach($info as $v){
                $ids[] = $v->type_id;
            }
            $re = ArticleType::wherein('id',$ids)->get();
            return $re;
        }else{
            return false;
        }
    }


    /*
     * 根据id获取文章分类
     *
     * By Yuyang
     *
     * 2019-01-02
     */
    public static function getById($id)
    {
        $type = ArticleType::where('id', '=', $id)->first();
        return $type;
    }

    /*
     * 根据parent_id获取文章分类
     *
     * By yulong
     *
     * 2018-12-04
     */
    public static function getByFId($parent_id)
    {
        $type = ArticleType::where('parent_id', '=', $parent_id)->first();
        return $type;
    }

    /*
     * 根据parent_id获取文章分类
     *
     * By yulong
     *
     * 2018-12-04
     */
    public static function getByFIdCon($parent_id)
    {
        $type = ArticleType::where('parent_id', '=', $parent_id)->get();
        return $type;
    }

    /*
     * 根据条件检索文章分类
     *
    * By Yuyang
     *
     * 2019-01-02
     */
    public static function getListByCon($con_arr, $is_paginate)
    {
        $articles = new ArticleType();
        //相关条件
        if (array_key_exists('search_word', $con_arr) && !Utils::isObjNull($con_arr['search_word'])) {
            $keyword = $con_arr['search_word'];
            $articles = $articles->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%");
            });
        }
        if (array_key_exists('title', $con_arr)) {
            $articles = $articles->where('title', '=', $con_arr['title']);
        }
        if (array_key_exists('author', $con_arr)) {
            $articles = $articles->where('author', '=', $con_arr['author']);
        }
        $articles = $articles->orderby('seq', 'asc')->orderby('id', 'desc');
        if ($is_paginate) {
            $articles = $articles->paginate(Utils::PAGE_SIZE);
        } else {
            $articles = $articles->get();
        }
        return $articles;
    }

    /*
     * 设置文章分类信息，用于编辑
     *
    * By yulong
     *
     * 2018-12-04
     */
    public static function setInfo($type, $data)
    {
        if (array_key_exists('id', $data)) {
            $type->id = array_get($data, 'id');
        }
        if (array_key_exists('parent_id', $data)) {
            $type->parent_id = array_get($data, 'parent_id');
        }
        if (array_key_exists('name', $data)) {
            $type->name = array_get($data, 'name');
        }
         if (array_key_exists('description', $data)) {
             $type->description = array_get($data, 'description');
         }
        if (array_key_exists('is_option', $data)) {
            $type->is_option = array_get($data, 'is_option');
        }
        if (array_key_exists('is_tag', $data)) {
            $type->is_tag = array_get($data, 'is_tag');
        }
        if (array_key_exists('seq', $data)) {
            $type->seq = array_get($data, 'seq');
        }
        if (array_key_exists('status', $data)) {
            $type->status = array_get($data, 'status');
        }
        if (array_key_exists('ill_id', $data)) {
            $type->ill_id = array_get($data, 'ill_id');
        }
        if (array_key_exists('created_at', $data)) {
            $type->created_at = array_get($data, 'created_at');
        }
        if (array_key_exists('updated_at', $data)) {
            $type->updated_at = array_get($data, 'updated_at');
        }
        if (array_key_exists('deleted_at', $data)) {
            $type->deleted_at = array_get($data, 'deleted_at');
        }
        return $type;
    }

    /*
    * 递归查询所有父文章分类
    *
    * By yulong
     *
     * 2018-12-04
        */
//    public static function getfatherills($ids,$parent_ids)
//    {
//        foreach ($ids as $id) {
//            $parent_id=ArticleType::where('id', '=', $id)->first()->parent_id;
//            if($parent_id!='0'&&$parent_id!=""){
//            array_push($medicine_fa_ids, $parent_id);
//                self::getfatherills([$medicine_fa_ids],$medicine_fa_ids);
//            }
//        }
//        return $medicine_fa_ids;
//    }

    /*
     * 根据最高父类id,查询其下所有子类的id
     *
     * By Yuyang
     *
     * 2018-12-21
     */
    public static function getByfatherAllId($id,$ids){
        $type = ArticleType::where('parent_id','=',$id)->get();
        if(!empty($type)){
            foreach($type as $v){
                array_push($ids, $v['id']);
                $re = self::getByfatherAllId($v['id'],$ids);
                $ids = array_merge($ids,$re);
            }
        }
        return $ids;
    }

    /*
     * 根据任意文章分类的父id,获取最高父类id
     *
     * By Yuyang
     *
     * 2018-12-21
     */
    public static function getByorfatherAllId($parent_id){
        $type = ArticleType::where('id','=',$parent_id)->first();
        $parent_id = 0;
        if($type->parent_id != 0){
            $parent_id = self::getByorfatherAllId($type->parent_id);
        }
        return $parent_id;
    }
    

    /*
     * 根据排序范围查找文章分类
     *
     * By Yuyang
     *
     * 2018-12-20
     */
    public static function getBySeq($con_arr, $type)
    {
        $mulu = new ArticleType();
        //相关条件
        if (array_key_exists('parent_id', $con_arr) && !Utils::isObjNull($con_arr['parent_id'])) {
            $mulu = $mulu->where('parent_id', '=', $con_arr['parent_id']);
        }
        if ($type == 'down') {
            $mulu = $mulu->where('seq', '>', $con_arr['seq']);
            $mulu = $mulu->where('seq', '<=', $con_arr['seq_down']);
        } else {
            $mulu = $mulu->where('seq', '<', $con_arr['seq']);
            $mulu = $mulu->where('seq', '>=', $con_arr['seq_up']);
        }
        $mulu = $mulu->orderby('seq', 'asc');
        $mulu = $mulu->get();
        return $mulu;
    }

}