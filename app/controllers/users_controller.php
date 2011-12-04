<?php
class UsersController extends AppController {
	function login() {
	}
	
	function logout() {
		$this->redirect($this->Auth->logout());
	}
}
?>