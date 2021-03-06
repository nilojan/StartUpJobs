<?php

class EmployeeController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			/*array('allow',
                  'actions'=>array('registration'),
                  'users'=>array('*'),  
                  ),*/
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Employee;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Employee']))
		{
			$model->attributes=$_POST['Employee'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->EID));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Employee']))
		{
			$model->attributes=$_POST['Employee'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->EID));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Employee');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Employee('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Employee']))
			$model->attributes=$_GET['Employee'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Employee::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='employee-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	public function actionRegistration() {
        $model = new RegistrationForm;
        if (isset($_POST['RegistrationForm'])) {
              $model->attributes = $_POST['RegistrationForm'];
              if ($model->validate()) {       //generate activation key
               $activationKey = mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
                // $model->activationKey= mt_rand() . mt_rand() . mt_rand() . mt_rand() . mt_rand();
                        $key = 'AG*@#(129)!@K.><>]{[|sd`rjenfla0847&($#)!$Masdc$#@';
                        $pwd = hash('sha512', $key . ($model->password));
                        $pwd = substr($pwd, 0, 100);
                        $record = new user;
                        // Save into user
                        $record->username = $model->username;
                        $record->password = $pwd;
                        $record->name = $model->name;
                        $record->email = $model->email;
                        if ($record->save())	{
                                $message = new YiiMailMessage;
                                $baseUrl = Yii::app()->request->baseUrl;
                                $serverPath = 'localhost/yii/uStyle';
                                $body = "Hi <font type=\"bold\">" . $record->name . "</font><br>
                                <br>
                                Welcome to StartUp Jobs Asia! Your account <font type=\"bold\">" . $record->username . "</font> has been registered.<br>
                                <br>
                                In order to ensure that you have received this confirmation, we ask that you follow the link below and confirm that this is in fact the correct email address.<br>
                                <br>
                                <a href=\"" . Yii::app()->baseURL . '/index.php/registration/verify?code=' . $activationKey . "\">Verify Your Email Here</a><br>
                                <br>
                                Accounts that have not been confirmed will be deactivated and removed from our system within 7 days, including all email addresses.<br> 
                                <br>
                                If you have NOT attempted to create an account at uStyle please ignore this email - it might have been sent because someone mistyped his/her own email address.<br>
                                THIS IS AN AUTO-GENERATED MESSAGE - PLEASE DO NOT REPLY TO THIS MESSAGE!<br>
                                <br>
                                -------------<br>
                                StartUp Jobs Asia Team";
                                $message->setBody($body, 'text/html');
                                $message->subject = "StartUp Jobs Asia Account Verification";
                                $message->addTo($model->email);
                                $message->from = 'noreply@startupjobs.asia';
               //                 Yii::app()->mail->send($message);
                                $this->redirect(array('site/page', 'view' => 'success'));
			}
		}
        }
        $this->render('registration', array('model' => $model));
    }
}
