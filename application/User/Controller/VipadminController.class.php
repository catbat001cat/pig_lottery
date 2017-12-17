<?php
namespace User\Controller;

use Common\Controller\AdminbaseController;

class VipadminController extends AdminbaseController {
	
	public function index(){
		$vip_model=M("vip");
		
		$count=$vip_model->where($where)->count();
		$page = $this->page($count, 20);
		
		$list = $vip_model
		->where($where)
		->order("level asc")
		->limit($page->firstRow . ',' . $page->listRows)
		->select();
		
		$this->assign('list', $list);
		$this->assign("page", $page->show('Admin'));
		
		$this->display("");
	}
	
	public function add() {
		$this->display();
	}
	
	public function add_post() {
		$vip_model=M("vip");
		
		$level = I("post.level");
		$price = I("post.price");
		
		$data = array(
				'level' => $level,
				'price' => $price
		);
		
		$vip_model->add($data);
		
		$this->success("添加成功！");
	}
	
	public function edit($id) {
		$vip_model=M("vip");
		
		$vo = $vip_model->where("id=$id")->find();
		
		$this->assign('vo', $vo);
		
		$this->display();
	}
	
	public function edit_post() {
		$vip_model=M("vip");
		
		$id = I("post.id");
		$level = I("post.level");
		$price = I("post.price");
		
		$data = array(
			'level' => $level,
			'price' => $price
		);
		
		$vip_model->where("id=$id")->save($data);
		
		$this->success("修改成功！");
	}
	
	public function delete($id) {
		$vip_model=M("vip");
		
		$vip_model->where("id=$id")->delete();
		
		$this->success("删除成功");
	}
}
