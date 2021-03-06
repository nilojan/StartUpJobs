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
                  'actions'=>array('apply','search'),
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
       if ($company->status == 0) {
            $this->redirect(array('site/page/view/notApproved'));
        }

       if (isset($_POST['JobForm'])) {
                       
                       $model->attributes = $_POST['JobForm'];
                       if($_POST['JobForm']['full_time'] == '0')
                       {
                            $model->full_time = null;
                       }
                       else
                       {
                            $model->full_time = $_POST['JobForm']['full_time'];
                       }

                       if($_POST['JobForm']['part_time'] == '0')
                       {
                            $model->part_time = null;
                       }
                       else
                       {
                            $model->part_time = $_POST['JobForm']['part_time'];
                       }

                       if($_POST['JobForm']['freelance'] == '0')
                       {
                            $model->freelance = null;
                       }
                       else
                       {
                            $model->freelance = $_POST['JobForm']['freelance'];
                       }

                       if($_POST['JobForm']['internship'] == '0')
                       {
                            $model->internship = null;
                       }
                       else
                       {
                            $model->internship = $_POST['JobForm']['internship'];
                       }

                       if($_POST['JobForm']['temporary'] == '0')
                       {
                            $model->temporary = null;
                       }
                       else
                       {
                            $model->temporary = $_POST['JobForm']['temporary'];
                       }
                                             
                      
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
                                            
                                            $company->job_post_balance--;
                                            $company->save();


                                            $JID=$record->JID;             
                                            $feed = new feeds;
                                            $feed->title = "{$record->title} {$company->cname} {$record->location}";
                                            $feed->description=substr($record->description, 0, 70);
                                            $feed->url = "{$record->title} {$company->cname} {$record->location}";
                                            $feed->category = "{$record->category}";
                                            $feed->author =$company->cname ;
                                            $feed->JID = $record->JID;
                                            $feed->image = $company->image;
                                            
                                            $feed->save();

                                            // send email notification
                                            
                                            $usr = user::model()->find('ID=:ID', array('ID' => Yii::app()->user->getID()));
                                            
                                            $data = array(
                                            'job' => $job_title,
                                            'company' =>  $company->cname,                                                                                   
                                            //'to' => 'post@startupjobs.asia',
                                            'to' => 'sb9176@adzek.com',
                                            'job_url' => Yii::app()->getBaseUrl(true).'/job/job?JID='.$JID,

                                            );                              
                                            $sendEmail =  Yii::app()->user->sendEmail('submit_job',$data);
                                            
                                            $this->redirect(array('job/job','JID' => $JID));
                                            
                                          
                                       }
                            }   
                       }
                       else
                            $this->redirect(array('site/page', 'view'=>'notApproved'));
       }
       $this->render('submitJob', array('model' => $model,'job_post_balance'=>$company->job_post_balance));
    }
    //Not completed
    //Upgrade job posting to premium
    
    public function actionPremium($JID) {
        
        $ID = Yii::app()->user->getID();
        if($job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>$ID)))
        {
            Yii::app()->session['JID'] = $JID;
            $abc =  Yii::app()->session['JID'];
            $this->render('premium', array('JID'=>$JID, 'abc'=>$abc)); 
        }
        else
        {
            $this->render('premium', array('JID'=>$JID, 'abc'=>$abc));
        }
        
    }
    
    
    public function actionUpdate($JID) {
        $ID = Yii::app()->user->getID();
        $model = new JobForm;
        //$job = job::model()->with('company')->find('JID=:JID' ,  array(':JID' => $JID, ));
       
       //  if (!($favourite = favourite::model()->find('profileID=:profileID&&friendID=:friendID', array(':profileID' => $model1->profileID,
         //   ':friendID' => $model1->friendID))))
       
        //need to check company id as well...
        //potential error
          if(Yii::app()->user->isAdmin())
          {
              $job = job::model()->with('company')->find('JID=:JID',  array(':JID' => $JID));  
          }
          else
          {
              $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>$ID));
          }
        
        //CActiveRecord for old one
        if ($job !=null)
            $model->attributes = $job->attributes;
            $model->full_time= $job->full_time;
            $model->part_time= $job->part_time;
            $model->freelance= $job->freelance;
            $model->internship= $job->internship;
            $model->temporary= $job->temporary;

        //$model->about = str_replace('<br />', "", $company->about);
        if (isset($_POST['JobForm'])) {

                   
                    $model->attributes = $_POST['JobForm'];
                    
                    if(!(Yii::app()->user->isAdmin()))
                    {
                      if($_POST['JobForm']['full_time'] == '0')
                       {
                            $model->full_time = null;
                       }
                       else
                       {
                            $model->full_time = $_POST['JobForm']['full_time'];
                       }

                       if($_POST['JobForm']['part_time'] == '0')
                       {
                            $model->part_time = null;
                       }
                       else
                       {
                            $model->part_time = $_POST['JobForm']['part_time'];
                       }

                       if($_POST['JobForm']['freelance'] == '0')
                       {
                            $model->freelance = null;
                       }
                       else
                       {
                            $model->freelance = $_POST['JobForm']['freelance'];
                       }

                       if($_POST['JobForm']['internship'] == '0')
                       {
                            $model->internship = null;
                       }
                       else
                       {
                            $model->internship = $_POST['JobForm']['internship'];
                       }

                       if($_POST['JobForm']['temporary'] == '0')
                       {
                            $model->temporary = null;
                       }
                       else
                       {
                            $model->temporary = $_POST['JobForm']['temporary'];
                       }  
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
                    }
                       
                    
				        	 $job_title = str_replace('/','-',$model->title);
					
                    $job->title = $job_title;
                    $job->description = $model->description;
          					$job->url = $model->url;
          					$job->meta = $model->meta;
                    $job->meta_title = $model->meta_title;

          					
                    $job->modified = new CDbExpression('NOW()');
                    if ($job->save()) {      
                                       //redirect 
                              if(yii::app()->user->isAdmin())
                              {
                               $this->redirect(array('admin/manage')); 
                              }  

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
        $aplicants = Application1::model()->find('JID=:JID', array('JID'=> $JID));
        $total_applicants = count($aplicants);
        $today = date('Y-m-d H:i:s');
        $days_left = Yii::app()->user->dateDiff($today, $job->expire);

        //code from usercontroller.php -> actionApply_Job($JID)

        if(Yii::app()->user->isGuest)
        { 
             $model = new Employee();  

              /*  Using from Action Edit for myDate */
              $myDate = new myDate();
              $mydob = explode('-', $model->dob);
              
              //$myDate->year = $mydob[0];
              //$myDate->month = $mydob[1];
              //$myDate->day = $mydob[2];


              $contact = explode('-', $model->contact);
              $myDate->country_code = $contact[0];
              //$model->contact = $contact[1];

              
              if(isset($_POST['Employee'])) 
              {
                      $model->attributes = $_POST['Employee'];
                      $uploadedFile=CUploadedFile::getInstance($model,'resume');
                      $model->resume = $uploadedFile->name;
                      $saved = false;
                      if($existing_employee = Employee::model()->find('email=:email',array('email'=>$model->email)))
                      {
                            $existing_employee->fname = $model->fname;          
                            $existing_employee->lname = $model->lname;
                            $existing_employee->contact = $model->contact;
                            $existing_employee->coverLetter = $model->coverLetter;
                            $existing_employee->gender = $model->gender;
                            $existing_employee->country = $model->country;
                            $existing_employee->edu = $model->edu;
                            $existing_employee->resume = $model->resume;
                            $existing_employee->last_modified = date("d/m/y : H:i:s", time());
                            
                            $ID = $existing_employee->EID;
                            $check = Application1::model()->find(':ID=EID&&:JID=JID',array(':ID'=>$ID,':JID'=>$JID));
                            if($check)
                            {
                              //you have already applied for this job
                              echo "you have aready applied for this job!";
                              $this->redirect(array('site/page', 'view'=>'already-applied'));
                            }                                         

                  if($existing_employee->save())
                  {
                    $saved = true;
                    $fileName = str_replace(' ', '', "{$existing_employee->EID}-{$uploadedFile->name}");                                 
                                $uploadedFile->saveAs(Yii::app()->basepath.'/../jobApplication/'.$fileName);

                                $application = new Application1;
                              $job = job::model()->find('JID=:JID', array('JID' => $JID));
                              $company = company::model()->find('CID=:CID', array('CID' => $job->CID));
                              $application->cover_letter = nl2br($model->coverLetter);
                              $application->EID = $EID;
                                $application->JID = $JID;
                                $application->CID = $job->CID;
                                $application->resume = $model->resume;

                                if($application->save())  
                                {
                                  //send email
                                  $data = array(
                            'name' => $existing_employee->name,
                            'job' => $job->title,
                            'company' =>  $company->cname,
                            'username' => $new_user->username,                        
                            'to' => $model->email,

                            );                    
                            $sendEmail =  Yii::app()->user->sendEmail('applyjob',$data);
                                }

                                $this->redirect(array('site/page', 'view'=>'success'));                                   
                  }          
                        
                      }
                      else
                      {
                          //first time guest user
                          $saved = true;
                                
                          $new_user = new user();
                          $new_user->username = $model->fname.rand(11,999);
                          
                          $key = 'AG*@#(129)!@K.><>]{[|sd`rjenfla0847&($#)!$Masdc$#@';
                                      $dt = date("dmY");
                                      $pwd = hash('sha512', $key.($dt));
                                      $pwd = substr($pwd, 0, 100);            
                          $new_user->password = $pwd;
                          
                          $new_user->email = $model->email;
                          $new_user->name = $model->fname;
                          $new_user->activation_key = mt_rand().mt_rand().mt_rand().mt_rand();

                          
                            /*  Using from Action Edit for myDate */
                            $myDate->attributes=$_POST['myDate'];
                            $myDate->day = $_POST['myDate']['day'];
                            $myDate->month = $_POST['myDate']['month'];
                            $myDate->year = $_POST['myDate']['year'];
                            $myDate->country_code = $_POST['myDate']['country_code'];
                            $model->contact = $myDate->country_code.'-'.$model->contact;
                          
                            $model->dob = $myDate->year.'-'.$myDate->month.'-'.$myDate->day;
                      
                              if($new_user->save())
                              {

                                $ID = Yii::app()->db->getLastInsertID();
                                $model->UID = $ID;                
                                  //$user = Employee::model()->find('EID=:ID',array('ID'=>$ID));
                                if($model->save())
                                {
                                  $baseUrl = Yii::app()->request->baseUrl;
                                          $serverPath = 'localhost/yii/uStyle';
                                          $verification_link = Yii::app()->getBaseUrl(true).'/user/verify/code/'.$new_user->activation_key;

                                  //send email                  
                                  $data = array(
                                      'name' => $new_user->name,
                                      'activation_key' => $verification_link,
                                      'verify_link' =>  $verification_link,
                                      'username' => $new_user->username,
                                      'password' => $dt,
                                      'to' => $model->email,
                                      );                    
                                  $sendEmail =  Yii::app()->user->sendEmail('registration',$data);
                                 
                                  $fileName = str_replace(' ', '', "{$model->EID}-{$uploadedFile->name}");                                 
                                  $uploadedFile->saveAs(Yii::app()->basepath.'/../jobApplication/'.$fileName);
                                                
                                  $EID = Yii::app()->db->getLastInsertID();
                                  $application = new Application1;
                                  $job = job::model()->find('JID=:JID', array('JID' => $JID));
                                  $company = company::model()->find('CID=:CID', array('CID' => $job->CID));
                                  $application->cover_letter = nl2br($model->coverLetter);
                                  $application->EID = $EID;
                                    $application->JID = $JID;
                                    $application->CID = $job->CID;
                                    $application->resume = $model->resume;

                                    if($application->save())
                                    {
                                      //send email
                                      $data = array(
                                'name' => $new_user->name,
                                'job' => $job->title,
                                'company' =>  $company->cname,
                                'username' => $new_user->username,                        
                                'to' => $model->email,

                                );                    
                                $sendEmail =  Yii::app()->user->sendEmail('applyjob',$data);
                                    }

                                    $this->redirect(array('site/page', 'view'=>'success'));

                              }
                          } 
                      }
                    }
                    $this->render('job', array('job' => $job,
                                   'company'=>$company,
                                   'total_applicants'=> $total_applicants,
                                   'days_left' => $days_left,
                                   'model'=>$model,
                                   'myDate'=>$myDate,
                                   'action'=>'applyjob'
                                   ));
                  }
                  if(Yii::app()->user->isMember())
                  {
                       $model = new ApplyJobForm;
                       $ID = Yii::app()->user->getID();
                       $user = Employee::model()->find('UID=:ID',array('ID'=>$ID));
                       $model->coverLetter = str_replace('<br />', "", $user->coverLetter);

                            if (isset($_POST['ApplyJobForm'])) {
                                //check if applied
                                $model->attributes = $_POST['ApplyJobForm'];
                                if ($model->validate()) {
                                    $check = Application1::model()->find(':ID=EID&&:JID=JID',array(':ID'=>$ID,':JID'=>$JID));
                                    $uploadedFile=CUploadedFile::getInstance($model,'resume');
                                // if the user did not upload a file and also no resume stored
                                     if ($check!=null||(empty($uploadfile)&&($user->resume == null)))  {    // already applied to the job
                                             $this->redirect(array('site/page', 'view'=>'error'));
                                     }
                                // redirect if no resume is found
                                    else 
                                    {
                                        $oldfilename = $user->resume;
                                        $application = new Application1;
                                        $job = job::model()->find('JID=:JID', array('JID' => $JID));
                                    
                                        $user->coverLetter = nl2br($model->coverLetter);
                                        $application->cover_letter = nl2br($model->coverLetter);
                                        $application->EID =$ID;
                                        $application->JID = $JID;
                                        $application->CID = $job ->CID; 
                                    // send resume to employer 
                                    //$user = user::model()->find(':ID=ID', array(':ID'=>$ID)); 
                                    if ($application->save()) 
                                    {    
                                          if (!empty($uploadedFile)) 
                                          {      //uploaded file is not empty
                                                    $AID = Yii::app()->db->getLastInsertID();
                                                    $fileName = str_replace(' ', '', "{$AID}-{$uploadedFile}");  // random number + file name
                                                    $application->resume = $fileName;
                                                    if ($application->save())   
                                                    {
                                                        $uploadedFile->saveAs(Yii::app()->basepath.'/../jobApplication/'.$fileName);
                                                        
                                                      // send email notification
                                                        $company = company::model()->find('CID=:CID', array('CID' => $job->CID));
                                                        $usr = user::model()->find('ID=:ID', array('ID' => $ID));
                                                        
                                                        $data = array(
                                                        'name' => $user->fname,
                                                        'job' => $job->title,
                                                        'company' =>  $company->cname,
                                                        'username' => $usr->username,                       
                                                        'to' => $model->email,

                                                        );                    
                                                        $sendEmail =  Yii::app()->user->sendEmail('applyjob_existing_user',$data);
                                                                      
                                                        $this->redirect(array('site/page', 'view'=>'success'));
                                                    }
                                            }           //uploaded file is empty
                                            else 
                                            {      //use previous resume
                                                    $fileName = $application->EID.'-'.$user->resume;
                                                    $application->resume =$fileName;
                                                    if ($application->save())   
                                                    {       // copy the file to the job application folder
                                                        copy(Yii::app()->basepath.'/../resume/'.$user->resume,Yii::app()->basepath.'/../jobApplication/'.$fileName);
                                                        
                                                        // send email notification
                                                        $company = company::model()->find('CID=:CID', array('CID' => $job->CID));
                                                        $usr = user::model()->find('ID=:ID', array('ID' => $ID));
                                                        
                                                        $data = array(
                                                        'name' => $user->fname,
                                                        'job' => $job->title,
                                                        'company' =>  $company->cname,
                                                        'username' => $usr->username,                       
                                                        'to' => $model->email,

                                                        );                    
                                                      $sendEmail =  Yii::app()->user->sendEmail('applyjob_existing_user',$data);
                                                      
                                                      $this->redirect(array('site/page', 'view'=>'success'));
                                                    }
                                            }    
                                        }
                                    }
                                 }
                              }
                               $this->render('job', array('user'=>$user,
                                                         'model'=>$model,
                                                         'job' => $job,
                                                         'company'=>$company,
                                                         'total_applicants'=> $total_applicants,
                                                         'days_left' => $days_left,));
                      }  

                  if(Yii::app()->user->isCompany())
                  {
                        $this->render('job', array(
                               'job' => $job,
                               'company'=>$company,
                               'total_applicants'=> $total_applicants,
                               'days_left' => $days_left,));
                       
                  } 

       
    }
    
    
    public function deleteJob($JID) {
        $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>Yii::app()->user->getID()));
        $job->delete();
       
    } 

    public function actionDelete($JID) {
        $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>Yii::app()->user->getID()));
        $job->delete();
        $this->redirect(array('job/manageJobs'));
       
    } 
  
    public function actionJobList()  {
        $CID = Yii::app()->user->getID();
        //$jobList=job::model()->findAll('CID=:CID',array('CID'=>$CID,
        $jobList = new Job();                                         
        $this-> render('jobList', array('jobList' =>$jobList));
    }

   
    public function actionBuy($JID){
 
        // set 
        $paymentInfo['Order']['theTotal'] = 5.00;
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

    public function actionSearch()
    {
       
        $query = $_GET['q'];

       // $this->redirect(array('site/page','view'=>'success'));
 
        $this->render('search', array('query'=>$query));
       
    }
 
    public function actionConfirmPayment()  {
                $JID = Yii::app()->session['JID'];
               // echo $JID;
                //$job = job::model()->find('JID=:JID',  array('JID' => $JID, ));
                $job = job::model()->with('company')->find('JID=:JID&&ID=:ID',  array(':JID' => $JID, ':ID'=>Yii::app()->user->getID()));
                $company = company::model()->find('CID=:CID',array(':CID'=>$job->CID));
                $user = user::model()->find('ID=:ID',array(':ID'=>Yii::app()->user->getID()));
                $job->premium = 1;
                $job->save();

                $data = array(
                    'name' => $user->name,
                    'job' => $job->title,
                    'job_url' => Yii::app()->getBaseUrl(true).'/job/job?JID='.$job->JID,
                    'company' =>  $company->cname,
                    'username' => $user->username,
                    'to' => $user->email,
                );                              
                $sendEmail =  Yii::app()->user->sendEmail('premium_job',$data);
                Yii::app()->session['JID'] = null;
               // var_dump($sendEmail); die;
                $this->redirect(array('job/manageJobs'));
                //$this->render('confirm');
                //$this->redirect(array('site/page','view'=>'success'));
    }
}
?>
