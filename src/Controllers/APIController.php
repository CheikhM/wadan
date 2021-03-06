<?php
/**
 * Created by PhpStorm.
 * User: inejih
 * Date: 30/08/2019
 * Time: 18:30
 **/

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Mahdra;
use App\Core\Request;
use App\Services\AuthService;
use App\Services\ProjectService;
use hijri;

class APIController extends BaseController
{
    public $c_language;

    function __construct()
    {
        $this->c_language = \App\Core::getCurrentLanguage();
    }

    function groupBy($request) {
    
        global $db;
        
        $data = $request->data;

        $entity = $data['entity'];
        $attr = $data['attr'];

        $sql = 'SELECT COUNT(*) as sum, ' . $attr . ' FROM ' . $entity . ' WHERE deleted_at IS NULL GROUP BY ' . $attr;

        return $this->JSONResponse($db->rawQuery($sql));
    } 
    // list all projects
    public function Index($request)
    {
        $status = isset($request->data['status']) ? $request->data['status'] : 'all';
        
        return $this->JSONResponse(ProjectService::getAllProjects($status));
    }

    // list all projects codes
    public function Codes($request)
    {
        return $this->JSONResponse(ProjectService::getAllCodes(null));
    }

    // add new project
    public function addProject($request) {
        if($request->method !== 'POST') {
            return $this->JSONResponse(NULL);
        }
        // get the project from the request
        $data = Request::getJsonRequest(true);
        $id = ProjectService::addProject($data);

        $result = isset($id) ? ['pid' => $id] : NULL;
        return $this->JSONResponse($result);
    }

    // update existing project
    public function updateProject($request) {

        if($request->method !== 'POST') {
            return $this->JSONResponse(NULL);
        }
        // get the project from the request
        $data = Request::getJsonRequest(true);
        $projectID = $data['id'];
        unset($data['id']);

        $id = ProjectService::updateProject($data, $projectID);

        $result = isset($id) ? ['pid' => $id] : NULL;

        return $this->JSONResponse($result);
    }

    // soft delete a project
    public function deleteProject($request) {

        $projectID = (int) $request->data['id'];

        // todo change the method to delete
        if($request->method !== 'GET' || empty($projectID)) {
            return $this->JSONResponse(NULL);
        }

        $deleted = ProjectService::deleteProject($projectID);

        if($deleted) {
            return $this->JSONResponse(['id' => $projectID]);
        } else {
            return $this->JSONResponse(NULL);
        }
    }

    // soft delete a project
    public function deleteInvoice($request) {

        $invoiceID = (int) $request->data['id'];

        // todo change the method to delete
        if($request->method !== 'GET' || empty($invoiceID)) {
            return $this->JSONResponse(NULL);
        }

        $deleted = ProjectService::deleteInvoice($invoiceID);

        if($deleted) {
            return $this->JSONResponse(['id' => $invoiceID]);
        } else {
            return $this->JSONResponse(NULL);
        }
    }

    // get project using the id

    function getProjectById($request) {
        $projectID = (int) $request->data['id'];

        if($request->method !== 'GET' || empty($projectID)) {
            return $this->JSONResponse(NULL);
        }

        $project = ProjectService::getProjectById($projectID);
        if ($project) {
            return $this->JSONResponse($project);
        }

        return $this->JSONResponse(NULL);
    }

    function Login($request) {

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }

        $data = Request::getJsonRequest(true);
        $username = $data['username'];
        $pwd = $data['password'];

        $user = AuthService::isLoggedIn($username, $pwd);

        if ($user) {
            return $this->JSONResponse($user);
        }

        return $this->JSONResponse(NULL);
    }

    // login with google, to be tested for security issues
    public function loginWithEmail($request) {
       
        // get the user email
        $email = $request->data['email'];
        
        $user = AuthService::checkEmail($email);

        if($user) {
            return $this->JSONResponse($user);
        }

        return $this->JSONResponse(NULL);
    }

    // add new project
    public function newInvoice($request) {
        if($request->method !== 'POST') {
            return $this->JSONResponse(NULL);
        }
        // get the project from the request
        $data = Request::getJsonRequest(true);
        $id = ProjectService::addInvoice($data);

        $result = isset($id) ? ['icid' => $id] : NULL;
        return $this->JSONResponse($result);
    }

    // add new boq
    public function newBoq($request) {
        if($request->method !== 'POST') {
            return $this->JSONResponse(NULL);
        }
        // get the project from the request
        $data = Request::getJsonRequest(true);
        $id = ProjectService::addBoq($data);

        $result = isset($id) ? ['boqid' => $id] : NULL;
        return $this->JSONResponse($result);
    }

    // add new boq
    public function newGroup($request) {
        if($request->method !== 'GET') {
            return $this->JSONResponse(NULL);
        }

        $group = [];

        $text = $request->data['name'];
        $pid = $request->data['pid'];

        $group['project_id'] = $pid;
        $group['text'] = $text;

        $id = ProjectService::addGroup($group);

        $gid = isset($id) ? ['gid' => $id] : NULL;
        return $this->JSONResponse($gid);
    }
    // list add invoices
    public function Invoice($request) {
        $projectID = (int) $request->data['pid'];

        return $this->JSONResponse(ProjectService::getAllInvoices($projectID));
    }

    // get all bill of quantities
    public function getAllBOQs($request) {
        $projectID = (int) $request->data['pid'];
        return $this->JSONResponse(ProjectService::getAllBOQs($projectID));
    }
}
