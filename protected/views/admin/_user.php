<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>   

    <table class ="table">
         <div class ="span3">
                    <?php echo CHtml::link($data->cname, array('User/profile/'.$data->ID)) ; ?>
         </div> 
        
         <div class="btn-toolbar">
                    <?php $this->widget('bootstrap.widgets.TbButton', array(
                                                                            'label'=>'Edit',
                                                                            'type'=>'inverse', // null, 'primary', 'info', 'success', 'warning', 'danger' or 'inverse'
                                                                            //array('job/update'),    
                                                                            'url'=>Yii::app()->createUrl("user/edit/".$data->ID),)); ?>
                                                                           



                   
        </div> 
   </table> 

