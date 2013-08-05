<?php
class JobController extends Controller {
    public function filters()   {
        return array( 'accessControl' ); // perform access control for CRUD operations
    }
 
    public function accessRules()   {
        return array(
            array('allow', // allow authenticated users to access all actions
                  'roles'=>array('2'),
            ),
            array('allow',
                  'actions'=>array('apply'),
                  'users'=>array('*'),  
                    
            ),
            array('allow',
                  'actions'=>array('job'),
                  'users'=>array('*'),
            ),
            array('deny',
                  'users'=>array('*')),
            );
    }
    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }
    
    public function actionManageJobs() {
        
       $this->render('manageJobs');
    }
    /*Approve job post only if company is approved ( status = 1)
     * else redirect to not approved
     */
    public function actionSubmitJob() {
       $model = new JobForm;
       $company = company::model()->find('ID=:ID', array('ID' => Yii::app()->user->getID()));
       if ($company ->status == 0) {
            $this->redirect(array('site/index'));
        }
       if (isset($_POST['JobForm'])) {
                       
                       $model->attributes = $_POST['JobForm'];
                       if ($company ->status == 1) {
                            if ($model->validate()) {
                                      $record = new job;
									  
									  $job_title = str_replace('/','-',$model->title);
									  
                                      $record->title = $job_title;
                                      $record->description = $model->description;
									  $record->responsibility = $model->responsibility;
									  $record->requirement = $model->requirement;
									  $record->howtoapply = $model->howtoapply;
                                      $record->type = $model->type;
									  $record->full_time = $model->full_time;
									  $record->part_time = $model->part_time;
									  $record->freelance = $model->freelance;
									  $record->internship = $model->internship;
									  $record->temporary = $model->temporary;
                                      $record->salary = $model->salary;
                                      $record->location = $model->location;
                                      $record->category = $model->category;
									  $record->tags = $model->tags;
                                      $record->CID = $company->CID;
                                      //$time = new CDbExpression('NOW()');
                                      //$date = new DateTime($time);
                                      //$record->created = $date;
                                      //$record->expiration = $date->add(new DateInterval('P30D'));; 
                                      if ($record->save()) {      
                                            $JID=$record->JID;           //redirect  
                                            $feed = new feeds;
                                            $feed->title = "{$record->title} {$company->cname} {$record->location}";
                                            $feed->description=substr($record->description, 0, 70);
                                            $feed->url = "{$record->title} {$company->cname} {$record->location}";
                                            $feed->category = "{$record->category}";
                                            $feed->author =$company->cname ;
                                            $feed->JID = $record->JID;
                                            $feed->image = $company->image;
                                            
                                            $feed->save();
                                            
                                            $this->redirect(array('job/job','JID' => $JID));
                                            
                                          
                                       }
                            }   
                       }
                       else
                            $this->redirect(array('site/page', 'view'=>'notApproved'));
       }
       $this->render('submitJob', array('model' => $model));
    }
    //Not completed
    //Upgrade job posting to premium
    
    public function actionPremium($JID) {
        
        Yii::app()->session['JID'] = $JID;
        $abc =  Yii::app()->session['JID'];
        $this->render('premium', array('JID'=>$JID, 'abc'=>$abc));
    }
    
    
    public function actionUpdate($JID) {
        $ID = Yii::app()->user->getID();
        $model = new JobForm;
        //$job = job::model()->with('company')->find('JID=:JID' ,  array(':JID' => $JID, ));
       
       //  if (!($favourite = favourite::model()->find('profileID=:profileID&&friendID=:friendID', array(':profileID' => $model1->profileID,
         //   ':friendID' => $model1->friendID))))
       
        //need to check company id as well...
        //potential error
        $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>$ID));
        //CActiveRecord for old one
        if ($job !=null)
            $model->attributes = $job->attributes;
        //$model->about = str_replace('<br />', "", $company->about);
        if (isset($_POST['JobForm'])) {
                    $model->attributes = $_POST['JobForm'];
					
					$job_title = str_replace('/','-',$model->title);
					
                    $job->title = $job_title;
                    $job->description = $model->description;
					$job->responsibility = $model->responsibility;
					$job->requirement = $model->requirement;
					$job->howtoapply = $model->howtoapply;
                    $job->type = $model->type;
					$job->full_time = $model->full_time;
					$job->part_time = $model->part_time;
					$job->freelance = $model->freelance;
					$job->internship = $model->internship;
					$job->temporary = $model->temporary;
                    $job->salary = $model->salary;
					$job->location = $model->location;
					$job->category = $model->category;
					$job->tags = $model->tags;
                    $job->modified = new CDbExpression('NOW()');
                    if ($job->save()) {      
                                       //redirect  
                                       $this->redirect(array('job/job','JID' => $JID));
                                     }
                     
        }             
        $this->render('update', array('model' => $model, 
                                      'job' => $job, ));
    }
    
    public function actionJob($JID) {
        $job = job::model()->find('JID=:JID', array('JID' => $JID));
        $CID=$job->CID;
        $company = company::model()->find('CID=:CID', array('CID'=> $CID));
        $this->render('job', array('job' => $job,
                                   'company'=>$company) );
    }
    
    
    public function deleteJob($JID) {
        $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>Yii::app()->user->getID()));
        $job->delete();
       
    } 
  
    public function actionJobList()  {
        $CID = Yii::app()->user->getID();
        //$jobList=job::model()->findAll('CID=:CID',array('CID'=>$CID,
        $jobList = new Job();                                         
        $this-> render('jobList', array('jobList' =>$jobList));
    }

   
    public function actionBuy($JID){
 
        // set 
        $paymentInfo['Order']['theTotal'] = 10.00;
        $paymentInfo['Order']['description'] = "Some payment description here";
        $paymentInfo['Order']['quantity'] = '1';
 
        // call paypal 
        $result = Yii::app()->Paypal->SetExpressCheckout($paymentInfo); 
        //Detect Errors 
        if(!Yii::app()->Paypal->isCallSucceeded($result)){ 
            if(Yii::app()->Paypal->apiLive === true){
                //Live mode basic error message
                $error = 'We were unable to process your request. Please try again later';
            }else{
                //Sandbox output the actual error message to dive in.
                $error = $result['L_LONGMESSAGE0'];
            }
            echo $error;
            Yii::app()->end();
 
        }else { 
            // send user to paypal 
            $token = urldecode($result["TOKEN"]); 
            Yii::app()->session['JID'] = $JID;
            $payPalURL = Yii::app()->Paypal->paypalUrl.$token; 
            $this->redirect($payPalURL); 
        }
    }
 
    public function actionConfirm($JID)
    {
        $token = trim($_GET['token']);
        $payerId = trim($_GET['PayerID']);
 
 
 
        $result = Yii::app()->Paypal->GetExpressCheckoutDetails($token);
 
        $result['PAYERID'] = $payerId; 
        $result['TOKEN'] = $token; 
        $result['ORDERTOTAL'] = 10.00;
 
        //Detect errors 
        if(!Yii::app()->Paypal->isCallSucceeded($result)){ 
            if(Yii::app()->Paypal->apiLive === true){
                //Live mode basic error message
                $error = 'We were unable to process your request. Please try again later';
            }else{
                //Sandbox output the actual error message to dive in.
                $error = $result['L_LONGMESSAGE0'];
            }
            echo $error;
            Yii::app()->end();
        }else{ 
 
            $paymentResult = Yii::app()->Paypal->DoExpressCheckoutPayment($result);
            //Detect errors  
            if(!Yii::app()->Paypal->isCallSucceeded($paymentResult)){
                if(Yii::app()->Paypal->apiLive === true){
                    //Live mode basic error message
                    $error = 'We were unable to process your request. Please try again later';
                }else{
                    //Sandbox output the actual error message to dive in.
                    $error = $paymentResult['L_LONGMESSAGE0'];
                }
                echo $error;
                Yii::app()->end();
            }else{
                //payment was completed successfully
                $JID = Yii::app()->session['JID'];
                echo $JID;
                $job = job::model()->find('JID=:JID',  array('JID' => $JID, ));
                $job->premium = 1;
                $job->save();
                Yii::app()->session['JID'] = null;
                //$this->render('confirm');
             //   $this->redirect(array('site/page','view'=>'success'));
            }
 
        }
                
        
    }
 
    public function actionCancel()
    {
        //The token of the cancelled payment typically used to cancel the payment within your application
        $token = $_GET['token'];
        $this->redirect(array('site/page','view'=>'success'));
 
        //$this->render('cancel');
    }
 
    public function actionConfirmPayment()  {
                $JID = Yii::app()->session['JID'];
                echo $JID;
                $job = job::model()->find('JID=:JID',  array('JID' => $JID, ));
                $job->premium = 1;
                $job->save();
                Yii::app()->session['JID'] = null;
                //$this->render('confirm');
                $this->redirect(array('site/page','view'=>'success'));
    }
}
?>