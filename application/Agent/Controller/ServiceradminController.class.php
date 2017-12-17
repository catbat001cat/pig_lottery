<?php

/**
 * 渠道Ads管理
*/
namespace Agent\Controller;
use Common\Controller\AdminbaseController;
class ServiceradminController extends AdminbaseController {
    
    function index() {
    
        $model=M("servicer a");
        $count=$model->where()->count();
        $page = $this->page($count, 20);
        $lists = $model
        ->where()
        ->order("id DESC")
        ->limit($page->firstRow . ',' . $page->listRows)
        ->select();
        
        $data = M('servicer')->where()->find();
        
        $this->assign('lists', $lists);
        $this->assign("page", $page->show('Admin'));

        $this->display();
    }


    function add() {
    
        $this->display();
    }
    
    function add_post() {
        $db = M('servicer');
        
        if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
            foreach ($_POST['photos_url'] as $key=>$url){
                $photourl=sp_asset_relative_url($url);
                $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
            }
        }
        $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
    
        $data=array(
        	'type' => $_REQUEST['type'],
            'create_time' => date("Y-m-d H:i:s"),
        );
        
        $data['smeta']=json_encode($_POST['smeta']);
      
        $id = $db->add($data);
    
        $this->success('添加成功');
    }
    
    function edit($id) {
        $db = M('servicer');
    
        $item = $db->where('id=' . $id)->find();
    
        $this->assign("smeta",json_decode($item['smeta'],true));
        $this->assign('vo', $item);
    
        $this->display();
    }
    
    function edit_post() {
        $db = M('servicer');

        $id = $_REQUEST['id'];
        
        $data=array(
        	'type' => $_REQUEST['type'],
            'create_time' => date("Y-m-d H:i:s"),
        );
        
        if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
            foreach ($_POST['photos_url'] as $key=>$url){
                $photourl=sp_asset_relative_url($url);
                $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
            }
        }
        $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
        $data['smeta']=json_encode($_POST['smeta']);
        
        $db->where('id=' . $id)->save($data);
    
        $this->success('修改成功');
    }
    
    function delete($id) {
        $db = M('servicer');
    
        if ($db->where('id=' . $id)->count() ==  0)
            $this->error("删除失败！");
        else {
            $db->where('id=' . $id)->delete();
            $this->success("删除成功！");
        }
    }
}
