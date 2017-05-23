<?php
$dbhost="localhost";
$dbuser="root";
$dbpass="";
$db="marques2";

//make connection attempt to the database
$con=mysql_connect($dbhost,$dbuser,$dbpass) or die("Can not establish Connection");
if($con)
	mysql_select_db($db,$con) or die("Can not select Database");
else
	$dberror="unable to connect to database";
//end of making the attempt to connect to the database

//Function to Capitalize the first letter given a string;
function initialCap($val)
{
	$str = explode(" ",$val);
	$len = count($str);
	$i=$len;
	$strn="";
	$j=0;
	while($i>0)
	{
		$strn.=strtoupper(substr($str[$j],0,1))."".substr($str[$j],1)." ";
		$j++;
		$i--;
	}
	return $strn;
}
//end of function

//create directory for controler,model,views if they dont exist
@mkdir(getcwd()."/controler");
@mkdir(getcwd()."/model");
@mkdir(getcwd()."/views");

	$res=mysql_query("select table_name from information_schema.tables where table_schema='$db'",$con);
	while($row=mysql_fetch_array($res)){
		echo $row[0]."\n";
		
		//create a directory named as the table
		$ext=explode("_", $row[0]);
		@mkdir(getcwd()."/controler/");
                @mkdir(getcwd()."/model/");
                @mkdir(getcwd()."/views/");
                @mkdir(getcwd()."/views/theme/");
                @mkdir(getcwd()."/views/theme/".$row[0]);
                
		
/***********************************START OF CONTROLLER************************************************************************/
		$str="<?php defined('BASEPATH') OR exit('No direct script access allowed');\n";
		
		$str.="class ".trim(initialCap($row[0]))." extends MY_Controller\n";
		$str.="{				\n";
		
		$rs=mysql_query("desc ".$row[0],$con);
		
/***********************************START OF CONTROLLER CONSTRUCT************************************************************************/
		$str.="public function __construct()\n";
		$str.="{\n";
		$str.="		parent::__construct();\n";
		$str.="		$"."this->load->library(array('ion_auth','form_validation'));\n";
		$str.="		$"."this->load->helper(array('url','language'));\n";
		$str.="		$"."this->load->model('".$row[0]."_model');\n";
		$str.="		$"."this->form_validation->set_error_delimiters("."$"."this->config->item('error_start_delimiter', 'ion_auth'), $"."this->config->item('error_end_delimiter', 'ion_auth'));\n";
		$str.="		$"."this->lang->load('auth');\n";		
		$str.="}\n";
		
/***********************************END OF CONTROLLER CONSTRUCT************************************************************************/
/***********************************START OF INDEX************************************************************************************/
		$str.="function index()\n";
		$str.="{\n"; 
		$str.="		$"."this->data['page_title'] = '".trim(initialCap($row[0]))."';\n"; 
		$str.="		$"."this->data['".$row[0]."']=$"."this->".$row[0]."_model->get".trim(initialCap($row[0]))."();\n";
		$str.="		$"."this->data['main_content']='views/theme/".$row[0]."/".$row[0]."';\n";
		$str.="		$"."this->render_page('theme/".$row[0]."/".$row[0]."', $"."this->data)".";\n";
		$str.="}"."\n";

/***********************************END OF INDEX***********************************************************************************/

/***********************************START OF ALL**********************************************************************************/
		$str.="function ".$row[0]."()\n";
		$str.="{\n"; 
		$str.="		$"."this->data['page_title'] = '".trim(initialCap($row[0]))."';\n"; 
		$str.="		$"."this->data['".$row[0]."']=$"."this->".$row[0]."_model->get".trim(initialCap($row[0]))."();\n";
		$str.="		$"."this->data['main_content']='views/theme/".$row[0]."/".$row[0]."';\n";
		$str.="		$"."this->render_page('theme/".$row[0]."/".$row[0]."', $"."this->data)".";\n";
		$str.="}"."\n";

/***********************************END OF ALL**************************************************************************************/
/***********************************START OF CREATE FUNCTION************************************************************************/
		$str.="public function create_".$row[0]."()\n";
		$str.="{\n";
		$str.="	$"."this->data['title'] ='create ".$row[0]."';\n";

// 		$str.="	if (!$"."this->ion_auth->logged_in() || !$"."this->ion_auth->is_admin());\n";
// 		$str.="	{\n";
// 		$str.="		redirect('auth', 'refresh');\n";;
// 		$str.="	}\n";

		$str.="	$"."obj=(object)$"."_POST;\n";
		$str.="	$"."objs=get_object_vars($"."obj);\n";
		
		//validate form input
		echo $row[0]."gh";
		$rs=mysql_query("desc ".$row[0],$con);
		while($rw=mysql_fetch_array($rs)){
		$column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
		if($rw[2]=="NO" and empty($rw[5])){
		$str.="	$"."this->form_validation->set_rules('".$column->column_name."', '".$column->column_comment."', 'required');\n";	
		        }
		}		   
		//end of validating input
		//check if the validation was successfull and create an array with posted form
		$str.="	if ($"."this->form_validation->run() == true)\n";
		$str.="	{\n";
		                $rs=mysql_query("desc ".$row[0],$con);
		$str.="			$"."array= array(\n";
				while($rw=mysql_fetch_array($rs)){
			        $column=mysql_fetch_object(mysql_query("select c.column_name from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
		$str.="			'".$column->column_name."' => $"."this->input->post('".$column->column_name."'),\n";	         
			         }
	        $str.="			);\n";
	        
		$str.="		$".$row[0]."_data =$"."array;\n";
		$str.="	}\n";
		
		//Test if the insert was successfull 
		$str.="	if ($"."this->form_validation->run() == true && $"."this->".$row[0]."_model->create_".$row[0]."($".$row[0]."_data))\n";
		$str.="	{\n";
		$str.="		$"."this->session->set_flashdata('message', $"."this->ion_auth->messages());\n";
		$str.="		redirect(\"".$row[0]."/".$row[0]."\",'refresh');\n";
		$str.="	}\n";
		
		//set errors for and redisplay the form
		$str.="	else\n";
		$str.="	{\n";
		// display the create form
		// set the flash data error message if there is one
	        $str.="		$"."this->data['message'] = (validation_errors() ? validation_errors() : ($"."this->ion_auth->errors() ? $"."this->ion_auth->errors() : $"."this->session->flashdata('message')));\n";
	        $str.="		$"."this->data['csrf'] = $"."this->_get_csrf_nonce();\n";
	        $str.="		$"."this->data['obj'] =$"."objs;\n";
	        $str.="		$"."this->render_page('theme/".$row[0]."/create_".$row[0]."', $"."this->data);\n";
	        $str.="	}\n";
	     $str.="}\n"; 
          
/***********************************END OF CREATE FUNCTION************************************************************************/
/***********************************START OF EDIT FUNCTION************************************************************************/
               $str.="public function edit_".$row[0]."($"."id)\n";
               $str.="{\n";
               $str.="	$".$row[0]."= $"."this->".$row[0]."_model->get_".trim(initialCap($row[0]))."($"."id)->result();\n";
               $str.="	$"."this->data['".$row[0]."']=$".$row[0].";\n";
               $str.="	$"."obj=(object)$"."_POST;\n";
	       $str.="	$"."objs=get_object_vars($"."obj);\n";
	       // validate form input
	       $rs=mysql_query("desc ".$row[0],$con);
	       while($rw=mysql_fetch_array($rs)){
	       $column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	       if($rw[2]=="NO" and empty($rw[5])){
	       $str.="	$"."this->form_validation->set_rules('".$column->column_name."', '".$column->column_comment."', 'required');\n";	
		      }
	       }		   
	       //end of validate

              $str.="	if (isset($"."_POST) && !empty($"."_POST))\n";
              $str.="	{\n";
              $str.="		// do we have a valid request?\n";
              $str.="		if ($"."this->_valid_csrf_nonce() === FALSE || $"."id != $"."this->input->post('id'))\n";
              $str.="		{\n";
              $str.="			show_error($"."this->lang->line('error_csrf'));\n";
              $str.="		}\n";        

              $str.="		if ($"."this->form_validation->run() === TRUE)\n";
              $str.="		{\n";
              
	      $rs=mysql_query("desc ".$row[0],$con);
	      $str.="			$"."array= array(\n";
			      while($rw=mysql_fetch_array($rs)){
			      $column=mysql_fetch_object(mysql_query("select c.column_name from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	      $str.="			'".$column->column_name."' => $"."this->input->post('".$column->column_name."'),\n";	         
				}
	      $str.="			);\n";
	      
	      $str.="		$".$row[0]."_data =$"."array;\n";                
              $str.="		}\n";
              $str.="		// check to see if we are updating\n";
              $str.="		if($"."this->form_validation->run() == true &&  $"."this->".$row[0]."_model->update_".$row[0]."($"."id, $".$row[0]."_data))\n";
              $str.="		{\n";
              $str.="			$"."this->session->set_flashdata('message', $"."this->ion_auth->messages());\n";
              $str.="			redirect(\"".$row[0]."/".$row[0]."\", 'refresh');\n"; 
              $str.="		}\n";
              $str.="		else\n";
              $str.="		{\n";
              $str.="			$"."this->session->set_flashdata('message', $"."this->ion_auth->errors());\n";
              $str.="			redirect('".$row[0]."/edit_".$row[0]."/'.$".$row[0]."->id, 'refresh');\n";
              $str.="		}\n";
              $str.="	}\n";
              $str.="	else\n";
              $str.="	{\n";
              $str.="		$"."this->data['message'] = (validation_errors() ? validation_errors() : ($"."this->ion_auth->errors() ? $"."this->ion_auth->errors() : $"."this->session->flashdata('message')));\n";
              $str.="		$"."this->data['csrf'] = $"."this->_get_csrf_nonce();\n";
              $str.="		$"."this->render_page('theme/".$row[0]."/edit_".$row[0]."', $"."this->data);\n";
              $str.="	}\n";
              $str.="}\n";
/***********************************END OF EDIT FUNCTION************************************************************************/
/***********************************START OF DELETE FUNCTION************************************************************************/
              $str.="public function delete_".$row[0]."($"."value='')\n";
              $str.="{\n";
              $str.="	if($"."this->".$row[0]."_model->delete_".$row[0]."($"."value)==TRUE)\n";
              $str.="	{\n";
              $str.="	$"."this->data['message'] =  $"."this->session->set_flashdata('message','The ".$row[0]." has been successfuly removed');\n";
              $str.="	}\n";
              $str.="	redirect('".$row[0]."/".$row[0]."', 'refresh');\n";
              $str.="}\n";
/***********************************END OF DELETE FUNCTION************************************************************************/ 
              $str.="public function _get_csrf_nonce()\n";
              $str.="{\n";
	      $str.="	$"."this->load->helper('string');\n";
	      $str.="	$"."key   = random_string('alnum', 8);\n";
	      $str.="	$"."value = random_string('alnum', 20);\n";
	      $str.="	$"."this->session->set_flashdata('csrfkey', $"."key);\n";
	      $str.="	$"."this->session->set_flashdata('csrfvalue', $"."value);\n";

	      $str.="	return array($"."key => $"."value);\n";
              $str.="}\n";

              $str.="public function _valid_csrf_nonce()\n";
	      $str.="{\n";
	      $str.="	$"."csrfkey = $"."this->input->post($"."this->session->flashdata('csrfkey'));\n";
	      $str.="	if ($"."csrfkey && $"."csrfkey == $"."this->session->flashdata('csrfvalue'))\n";
	      $str.="	{\n";
	      $str.="		return TRUE;\n";
	      $str.="	}\n";
	      $str.="	else\n";
	      $str.="	{\n";
	      $str.="		return FALSE;\n";
	      $str.="	}\n";
              $str.="}\n";

              $str.="public function render_page($"."view, $"."data=null, $"."returnhtml=false)\n";
              $str.="{\n";

              $str.="	$"."this->viewdata = (empty($"."data)) ? $"."this->data: $"."data;\n";
       
              $str.="	$"."this->load->view('theme/header');\n";
              $str.="	$"."this->load->view('theme/sidebar');\n";
              $str.="	$"."this->load->view($"."view, $"."this->viewdata, $"."returnhtml);\n";
              $str.="	$"."this->load->view('theme/footer');\n";    
              $str.="}\n";
	      $str.="}\n";
/***********************************END OF CONTROLLER************************************************************************/
$fd=fopen("controler/".trim(initialCap($row[0])).".php","w");
fwrite($fd,$str);
fclose($fd);

/***********************************************************************************************************/
/***********************************************************************************************************/

/***********************************START OF MODEL************************************************************************/
            $str="";
            $str.="<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n";
            $str.="class ".trim(initialCap($row[0]))."_model extends CI_Model {\n";
            $str.="	//start of CONSTRUCT\n";
            $str.="	public function __construct()\n";
            $str.="	{\n";
            $str.="		// Call the CI_Model constructor\n";
            $str.="		parent::__construct();\n";
            $str.="	}\n";
            
            $str.="	//Retrieve all records\n"; 
	    $str.="	public function get".trim(initialCap($row[0]))."()\n";
	    $str.="	{\n";
            $str.="		$"."query = $"."this->db->get('".$row[0]."');\n";
            $str.="		return $"."query->result();\n";
	    $str.="	}\n";
	    
            $str.="	//insert data to table\n";
            $str.="	public function create_".$row[0]."($"."data)\n";
            $str.="	{\n";
            $str.="		if($"."this->db->insert('".$row[0]."', $"."data))\n";
	    $str.="		{\n";
            $str.="			return true;\n";
            $str.="		}\n";
            $str.="		else\n";
            $str.="		{\n";
            $str.="			return false;\n";
            $str.="		}\n";
            $str.="	}\n";

            $str.="	//return a particular row\n"; 
            $str.="	public function get_".$row[0]."($"."id)\n";
            $str.="	{\n"; 
            $str.="		return $"."this->db->get_where('".$row[0]."', array('id'=>$"."id));\n";     
            $str.="	}\n";
            
            $str.="	//update a particular record\n"; 
            $str.="	public function update_".$row[0]."($"."id, $"."data)\n";
            $str.="	{\n";
            $str.="		$"."this->db->where('id', $"."id);\n";
            $str.="		if ($"."this->db->update('".$row[0]."', $"."data))\n"; 
            $str.="		{\n";
            $str.="			return TRUE;\n";
            $str.="		}\n";
	    $str.="		else\n";
	    $str.="		{\n";
	    $str.="			return FALSE;\n";
            $str.="		}\n";
            $str.="	}\n";
            
            $str.="	//delete a record\n"; 
            $str.="	public function delete_".$row[0]."($"."id='')\n";
            $str.="	{\n";
            $str.="		$"."this->db->where('id', $"."id);\n";
	    $str.="		if ($"."this->db->delete('".$row[0]."'))\n"; 
	    $str.="		{\n";
	    $str.="			return TRUE;\n";
	    $str.="		}\n";
	    $str.="		else\n";
	    $str.="		{\n";
	    $str.="			return FALSE;\n";
	    $str.="		}\n";
	    $str.="	}\n";
	    $str.="}\n";
/***********************************END OF MODEL FUNCTIONS************************************************************************/
$fd=fopen("model/".trim(initialCap($row[0]))."_model.php","w");
fwrite($fd,$str);
fclose($fd);
/***********************************END OF MODEL************************************************************************/
/***********************************************************************************************************
***********************************************************************************************************
***********************************START OF VIEWS************************************************************************/
///////////////////////////////////START OF LIST VIEW////////////////////////////////////////////////////////////////////
            $str="";
            $str.="<!-- Main content -->\n";
            $str.="<section class=\"content\">\n";
	    $str.="<!-- Default box -->\n";
	    $str.="	<div class=\"box box-success\">\n";
	    $str.="		<div class=\"box-header with-border\">\n";
	    $str.="			<h3 class=\"box-title\">".$row[0]." List</h3>\n";
	    $str.="			<div class=\"box-tools pull-right\">\n";
	    $str.="				<div class=\"btn-group\">\n";
	    $str.="					<a href=\"<?= site_url('".$row[0]."/create_".$row[0]."/'); ?>\"><i class=\"fa fa-plus\"></i>Add ".trim(initialCap($row[0]))."</a>\n";
            $str.="				</div>\n";
            $str.="			</div>\n";
	    $str.="		</div>\n";
	    $str.="		<div class=\"box-body\">\n";
	    $str.="		<?php if(isset($"."message)){ ?>\n";
	    $str.="			<div class=\"alert alert-success\"><?= $"."message ?></div>\n";
	    $str.="		<?php } ?>\n";
            $str.="				<table  class=\"table table-bordered table-striped\" id=\"example1\">\n";
	    $str.="					<thead>\n";
	    $str.="						<tr>\n";
	    $rs=mysql_query("desc ".$row[0],$con);
	    while($rw=mysql_fetch_array($rs)){
	    $column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	    $str.="							<th>$column->column_comment</th>\n";
	    }	
	    $str.="							<th>&nbsp;</th>\n";
	    $str.="						</tr>\n";
	    $str.="					</thead>\n";
	    $str.="					<tbody>\n";
	    $str.="					<?php $"."i=0; ?>\n";
	    $str.="					<?php foreach ($".$row[0]." as $"."row): ?>\n";
	    $str.="					<?php $"."i++; ?>\n";
	    $str.="						<tr>\n";
	    $rs=mysql_query("desc ".$row[0],$con);
	    while($rw=mysql_fetch_array($rs)){
	    $column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	    if($column->column_name=='id'){
	    $str.="							<td><?= $"."i; ?></td>\n";
	    }else{
	    $str.="							<td><?= $"."row->".$column->column_name."; ?></td>\n";
	    }
	    }
	    
	    $str.="							<td><a href=\"<?= site_url('".$row[0]."/edit_".$row[0]."/'.$"."row->id); ?>\" data-toggle=\"tooltip\"  title=\"edit\" class=\"btn btn-info btn-xs\"><i class=\"fa fa-pencil\"></i></a>";
	    $str.="							<a href=\"<?= site_url('".$row[0]."/delete_".$row[0]."/'.$"."row->id); ?>\" data-toggle=\"tooltip\"  title=\"delete\" class=\"btn btn-danger btn-xs\"><i class=\"fa fa-trash\"></i></a></td>\n";         
	    $str.="						</tr>\n";
	    $str.="					<?php endforeach;?>\n";
	    $str.="					</tbody>\n";
	    $str.="				</table>\n";
	    $str.="			</div>\n";
	    $str.="		<!-- /.box-body -->\n";
	    $str.="	<div class=\"box-footer\">\n";      
	    $str.="	</div>\n";
	    $str.="	<!-- /.box-footer-->\n";
	    $str.="</div>\n";
	    $str.="<!-- /.box -->\n";
	    
$fd=fopen("views/theme/".$row[0]."/".$row[0].".php","w");
fwrite($fd,$str);
fclose($fd);
///////////////////////////////////END OF LIST VIEW////////////////////////////////////////////////////////////////////
///////////////////////////////////START OF CREATE VIEW////////////////////////////////////////////////////////////////////
           $str="";
           $str.="<!-- Main content -->\n";
           $str.="	<section class=\"content\">\n";
           $str.="		<div class=\"row\">\n";
           $str.="			<div class=\"col-xs-12\">\n";
           $str.="			<form class=\"form-horizontal\" method=\"post\" action=\"<?= site_url('".$row[0]."/create_".$row[0]."') ?>\">\n";
	   $str.="				<div class=\"box box-success\">\n";
           $str.="					<div class=\"box-header with-border\">\n";
	   $str.="						<h3 class=\"box-title\">Add ".trim(initialCap($row[0]))."</h3>\n";
	   $str.="					</div>\n";
           $str.="					<div class=\"box-body\">\n";
           $rs=mysql_query("desc ".$row[0],$con);
	   while($rw=mysql_fetch_array($rs)){
	   $column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	   if($column->column_name!="id"){
	   $str.="						<div class=\"form-group\">\n";
           $str.="							<label class=\"col-sm-2 control-label\">$column->column_comment";
           if($rw[2]=="NO" and empty($rw[5])){
	   $str.="<span style=\"color:red\">*</span>";
		}
           $str.="</label>\n";
           $str.="							<div class=\"col-sm-10\">\n";
           if($rw[1]=="text"){
	   $str.="								<textarea class=\"form-control\" name=\"$column->column_name\"><?php if(!empty($"."obj['$column->column_name'])){ echo $"."obj['$column->column_name'];} ?></textarea>\n";
	   }
	   elseif(substr($rw[1],0,4)=="enum"){
           $sub=substr($rw[1], 5);
           $sub=substr($sub, 0, -1);
           $sub=str_replace("'", "", $sub);
	   $subs=explode(",", $sub);
	   $i=0;
	   while($i<count($subs)){
	   $str.="								<input class=\"\" type=\"radio\" name=\"$column->column_name\" id=\"$column->column_name\" value='$subs[$i]' <?php if(!empty($"."obj['$column->column_name'])){ if($"."obj['$column->column_name']=='$subs[$i]'){echo\"checked\";}}?>>".initialCap($subs[$i])."\n";
			    $i++;
		    }
	    }
	   else{
		if($rw[1]=="date"){
	   $str.="								<input  type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" class=\"date_input\" size=\"12\" readonly value=\"<?php if(!empty($"."obj['$column->column_name'])){ echo $"."obj['$column->column_name'];} ?>\">\n";
		}
		if($rw[1]=="datetime"){
	   $str.="								<input type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" class=\"form_datetime\" size=\"12\" readonly value=\"<?php if(!empty($"."obj['$column->column_name'])){ echo $"."obj['$column->column_name'];} ?>\">\n";
		}
	        else if(substr($rw[1],0,5)=="float" or substr($rw[1],0,6)=="double"){
	   $str.="								<input class=\"form-control\" type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\"  size=\"12\" value=\"<?php if(!empty($"."obj['$column->column_name'])){ echo $"."obj['$column->column_name'];} ?>\">\n";
		}
	        else{
	   $str.="								<input class=\"form-control\" type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" value=\"<?php if(!empty($"."obj['$column->column_name'])){ echo $"."obj['$column->column_name'];} ?>\">\n";
	        }			    
           }           
           $str.="								<span class=\"alert-msg  error\"><?php echo form_error('$column->column_name'); ?></span>\n";
           $str.="							</div>\n";
           $str.="						</div>\n";
           }
           }
           $str.="						<div class=\"box-footer\">\n";
           $str.="						<button type=\"reset\" class=\"btn  btn-danger pull-left\"> cancel </button>\n";
           $str.="						<?php echo form_submit('submit', 'Save','class=\"btn btn-lg btn-success pull-right\"');?>\n";
           $str.="						</div>\n";
           $str.="					</div><!-- /.box-body -->\n";
           $str.="				</div><!--box box-success-->\n";
           $str."			<?php echo form_close();?>\n";
           $str.="			</div>\n";
           $str.="		</div>\n";
           $str.="	</section>\n";
           $str.="<script type=\"text/javascript\">\n";
	   $str.="	$(\".form_datetime\").datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});\n";
           $str.="</script>\n";
           
$fd=fopen("views/theme/".$row[0]."/create_".$row[0].".php","w");
fwrite($fd,$str);
fclose($fd);           
///////////////////////////////////END OF CREATE VIEW////////////////////////////////////////////////////////////////////
///////////////////////////////////START OF EDIT VIEW////////////////////////////////////////////////////////////////////
           $str="";
           $str.="<?php foreach ($".$row[0]." as $"."obj){}; ?>\n";
           $str.="<!-- Main content -->\n";
           $str.="	<section class=\"content\">\n";
           $str.="		<div class=\"row\">\n";
           $str.="			<div class=\"col-xs-12\">\n";
           $str.="			<form class=\"form-horizontal\" method=\"post\" action=\"<?= site_url('".$row[0]."/edit_".$row[0]."/'.$"."obj->id) ?>\">\n";
	   $str.="				<div class=\"box box-success\">\n";
           $str.="					<div class=\"box-header with-border\">\n";
	   $str.="						<h3 class=\"box-title\">Edit ".trim(initialCap($row[0]))."</h3>\n";
	   $str.="					</div>\n";
           $str.="					<div class=\"box-body\">\n";
           $str.="					<input class=\"form-control\" name=\"id\" id=\"id\" type=\"hidden\" value=\"<?php echo $"."obj->id; ?>\" />\n";
           $rs=mysql_query("desc ".$row[0],$con);
	   while($rw=mysql_fetch_array($rs)){
	   $column=mysql_fetch_object(mysql_query("select c.column_name,c.column_comment from information_schema.columns c where c.column_name='$rw[0]' and c.table_name='$row[0]' and c.table_schema='$db'"));
	   if($column->column_name!="id"){
	   $str.="						<div class=\"form-group\">\n";
           $str.="							<label class=\"col-sm-2 control-label\">$column->column_comment";
           if($rw[2]=="NO" and empty($rw[5])){
	   $str.="<span style=\"color:red\">*</span>";
		}
           $str.="</label>\n";
           $str.="							<div class=\"col-sm-10\">\n";
           if($rw[1]=="text"){
	   $str.="								<textarea class=\"form-control\" name=\"$column->column_name\"><?php echo $"."obj->$column->column_name; ?></textarea>\n";
	   }
	   elseif(substr($rw[1],0,4)=="enum"){
           $sub=substr($rw[1], 5);
           $sub=substr($sub, 0, -1);
           $sub=str_replace("'", "", $sub);
	   $subs=explode(",", $sub);
	   $i=0;
	   while($i<count($subs)){
	   $str.="								<input class=\"form-control\" type=\"radio\" name=\"$column->column_name\" id=\"$column->column_name\" value='$subs[$i]' <?php if($"."obj->$column->column_name=='$subs[$i]'){echo\"checked\";}?>>".initialCap($subs[$i])."\n";
			    $i++;
		    }
	    }
	   else{
		if($rw[1]=="date"){
	   $str.="								<input type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" class=\"date_input\" size=\"12\" readonly value=\"<?php echo $"."obj->$column->column_name; ?>\">\n";
		}
		if($rw[1]=="datetime"){
	   $str.="								<input type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" class=\"form_datetime\" size=\"12\" readonly value=\"<?php echo $"."obj->$column->column_name; ?>\">\n";
		}
	        else if(substr($rw[1],0,5)=="float" or substr($rw[1],0,6)=="double"){
	   $str.="								<input class=\"form-control\" type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\"  size=\"12\" value=\"<?php echo $"."obj->$column->column_name; ?>\">\n";
		}
	        else{
	   $str.="								<input class=\"form-control\" type=\"text\" name=\"$column->column_name\" id=\"$column->column_name\" value=\"<?php echo $"."obj->$column->column_name; ?>\">\n";
	        }			    
           }           
           $str.="								<span class=\"alert-msg  error\"><?php echo form_error('$column->column_name'); ?></span>\n";
           $str.="							</div>\n";
           $str.="						</div>\n";
           }
           }          
           $str.="						<div class=\"box-footer\">\n";
           $str.="						<?php echo form_hidden($"."csrf); ?>\n";
           $str.="						<button type=\"reset\" class=\"btn  btn-danger pull-left\"> cancel </button>\n";
           $str.="						<?php echo form_submit('submit', 'Update','class=\"btn btn-lg btn-success pull-right\"');?>\n";
           $str.="						</div>\n";
           $str.="					</div><!-- /.box-body -->\n";
           $str.="				</div><!--box box-success-->\n";
           $str."			<?php echo form_close();?>\n";
           $str.="			</div>\n";
           $str.="		</div>\n";
           $str.="	</section>\n";
           $str.="<script type=\"text/javascript\">\n";
	   $str.="	$(\".form_datetime\").datetimepicker({format: 'yyyy-mm-dd hh:ii:ss'});\n";
           $str.="</script>\n";
           
$fd=fopen("views/theme/".$row[0]."/edit_".$row[0].".php","w");
fwrite($fd,$str);
fclose($fd);           
///////////////////////////////////END OF EDIT VIEW////////////////////////////////////////////////////////////////////
/***********************************END OF VIEWS************************************************************************/
        }
?>
