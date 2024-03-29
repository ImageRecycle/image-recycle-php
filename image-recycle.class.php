<?php
class image_recycle_php {
    /**
     * Authentication values array
     * @var array
     */
    protected $auth = array();
    
    /**
     * Image Optimize API Url
     * @var string api url
     */
    protected $apiUrl = 'http://imageoptim/api/v1/';

    /**
     * Last Error message
     * @var string 
     */
    protected $lastError = null;


    /**
     * 
     * @param string $key
     * @param string $secret
     */
    public function __construct($key,$secret){
	$this->auth = array('key'=>$key, 'secret'=>$secret);	
    }
    
    /**
     * Change the API URL
     * @param string $url
     */
    public function setAPIUrl($url){
	$this->apiUrl = $url;
    }
    
    /**
     * Upload a file sent through an html post form
     * @param $_FILES $file posted file
     */
    public function uploadFile($file,$uploadParams=array()){
	if(class_exists('CURLFile')){
	    $curlFile = new CURLFile($file);
	}else{
	    $curlFile = '@'.$file;
	}	
	$params = array(
	    'auth' => json_encode($this->auth),
	    'file' => $curlFile,
	    'params' => json_encode($uploadParams)
	);
	try {
	    $result = $this->callAPI($this->apiUrl.'images/','POST',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Upload a file from an url
     * @param string $url
     * @return Object
     */
    public function uploadUrl($url,$params=array()){		
	$params = array(
	    'auth' => json_encode($this->auth),
	    'url' => $url,
	    'params' => json_encode((array)$params)
	);
	try {
	    $result = $this->callAPI($this->apiUrl.'images/','POST',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Call the API with curl
     * @param string $url
     * @param string $type HTTP method
     * @param array $datas 
     * @return type
     */
    protected function callAPI($url,$type,$datas){
	$curl = curl_init();	
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl, CURLOPT_TIMEOUT, 60);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
	$datas = array_merge($datas,array('XDEBUG_SESSION_START'=>'netbeans-xdebug'));
	if($type==='POST'){
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $datas);
	}else{
	    $url .= '?'.http_build_query($datas);
	}
	curl_setopt($curl, CURLOPT_URL, $url);
	$content = curl_exec($curl);
	$infos = curl_getinfo($curl);
	curl_close($curl);
	$infos['http_code'] = (String)$infos['http_code'];
	if($infos['http_code'][0]!=='2'){
	    $error = json_decode($content);
	    if(isset($error->errCode)){
		$errCode = $error->errCode;
	    }else{
		$errCode = 0;
	    }
	    if(isset($error->errMessage)){
		$errMessage = $error->errMessage;
	    }else{
		$errMessage = 'An error occurs';
	    }
	    throw new Exception($errMessage,$errCode);
	}
	if($infos['content_type']==='application/zip'){
	   return $content;
	}else{
	    return json_decode($content);
	}	
    }
    
    /**
     * Get all the images
     * @return type
     */
    public function getImagesList($offset=0, $limit=30,$ordering='time',$orderingDir='asc'){
	$params = array(	  
	    'auth' => json_encode($this->auth),
	    'offset' => $offset,
	    'limit' => $limit,
	    'ordering' => $ordering,
	    'ordering_dir' => $orderingDir
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'images/','GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Get one image
     * @param int $id
     * @return type
     */
    public function getImage($id){
	$params = array(
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'images/'.(int)$id,'GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Delete an image 
     * @param int $id
     * @return type
     */
    public function deleteImage($id){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'images/'.(int)$id,'DELETE',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Get account information
     * @return type
     */
    public function getAccountInfos(){
	$params = array(
	    'auth' => json_encode($this->auth),
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/mine','GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function getZipContent($ids,$type='optimized'){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	if(is_array($ids)){
	    implode(',', $ids);
	}
	
	try {
	    $result = $this->callAPI($this->apiUrl.'images/zip/'.$type.'/'.$ids,'GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function getSubAccountsList(){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/','GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
        
    public function getSubAccountInfos($id){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/'.$id,'GET',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function addSubAccount($datas){
	$params = array(
	    'auth' => json_encode($this->auth)
	);
	$params = array_merge($params,$datas);
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/','POST',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function updateSubAccount($id,$datas){
	$params = array(
	    'auth' => json_encode($this->auth)
	);
	$params = array_merge($params,$datas);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/'.$id,'PUT',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function deleteSubAccount($id){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/'.(int)$id,'DELETE',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    public function resetSubAccountSecret($id){
	$params = array(	
	    'auth' => json_encode($this->auth)
	);
	
	try {
	    $result = $this->callAPI($this->apiUrl.'accounts/sub/reset_secret/'.(int)$id,'PUT',$params);
	} catch (Exception $exc) {
	    $this->lastError = $exc->getMessage();
	    return false;
	}
	return $result;
    }
    
    /**
     * Get last error message
     * @return string
     */
    public function getLastError(){
	return $this->lastError;
    }
}

