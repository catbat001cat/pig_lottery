<?php
namespace Api\Controller;

use Common\Controller\AppframeController;

class GuestbookController extends AppframeController{
	
	protected $guestbook_model;
	
	public function _initialize() {
		parent::_initialize();
		$this->guestbook_model=D("Common/Guestbook");
	}

}