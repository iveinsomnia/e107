<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Upload file shortcode
*/

/**
 * @package e107
 * @subpackage shortcodes
 * @version $Revision$
 * @todo uploadfile shortcode - JS/Flash upload, optional allow image resize (see news administration), optional move by filetype (processing) - similar to Media Manager
 *
 * Print out upload form elements and/or process submitted uploads.
 */

/**
 * Print out upload form elements and/or process submitted uploads.
 * Your <form> tag must include: enctype='multipart/form-data' - in order to work.
 *
 * Example usage:
 * <code>
 * // Process uploaded file (sent by the form below), it'll print out message (if any)
 * if(isset($_POST['etrigger_uploadfiles']))
 * {
 * 		// NOTE: chmod permissions after upload are set to 0755
 * 		echo e107::getParser()->parseTemplate('{UPLOADFILE='.e_MEDIA.'public|process=1&upload_file_mask=jpg,jpeg,png,gif&upload_final_chmod=493}');
 * }
 *
 * // Render upload form
 * echo '<form action="'.e_SELF.'" enctype="multipart/form-data" method="post">';
 * echo e107::getParser()->parseTemplate('{UPLOADFILE='.e_MEDIA.'public|nowarn&trigger=etrigger_uploadfiles}');
 * echo '</form>';
 * </code>
 *
 * @todo Human readable *nix like permissions option (upload_final_chmod) e.g. 'rw-rw-r--' --> 0664, 'rwxrwxrwx' --> 0777
 *
 * @param string $parm upload_path|parameters (GET query format)
 * 	Available parameters:
 * 	- trigger [render] (string): name attribute of upload trigger button, default 'uploadfiles'
 * 	- name [render|processing] (string): name of upload (file) field, without array brackets ([]), default 'file_userfile'
 * 	- up_container [render] (string): the id attribute of upload container (containing upload field(s)), default 'up_container'
 * 	- up_row [render] (string): the id attribute of upload added fields (diuplicates), default 'upline'
 * 	- process [render|processing] ('0'|'1' boolean): main shortcode action, 0 - render markup, 1 - process uploaded files, default '0'
 *  - upload_file_mask [processing] (string): 'file_mask' parameter of process_uploaded_files() - comma-separated list of file types which if defined limits the allowed file types to those which are
 *  in both this list and the file specified by the 'filetypes' option. Enables restriction to, for example, image files. {@link process_uploaded_files()),
 *  default is empty string
 *  - upload_filetypes [processing] (string): 'filetypes' parameter of process_uploaded_files() - name of file containing list of valid file types, default is empty string
 * 	- upload_extra_file_types [processing] (string): 'extra_file_types' parameter of process_uploaded_files() - '0' (default) rejects totally unknown file extensions;
 *  '1' accepts totally unknown file extensions which are in $options['filetypes'] file; comma-separated list of additional permitted file extensions
 *	- upload_final_chmod [processing] (string): 'final_chmod' parameter of process_uploaded_files() - chmod() to be applied to uploaded files (0644 default).
 *	NOTE: you need to provide number with numerci base of decimal (as a string) which will be auto-converted to octal number
 *	Example: '493' --> 0755; '511' --> 0777
 *	- upload_max_upload_size [processing] (string): 'max_upload_size' parameter of process_uploaded_files() - maximum size of uploaded files in bytes,
 *	or as a string with a 'multiplier' letter (e.g. 16M) at the end, default is empty string
 *	- upload_overwrite [processing] ('0'|'1' boolean): 'overwrite' parameter of process_uploaded_files() - maximum number of files which can be uploaded - default is '0' (unlimited)
 *	- return_type [processing] ('0'|'message'|'result'): 'message' (default) - return messages (eMessage::render() method);
 *	'result' - return array generated by process_uploaded_files();
 *	'0' - return empty string;
 *	NOTE: upload messages are added to 'upload_shortcode' message namespace
 *	<code>
 *	// render messages manually (return_type=0)
 *	echo e107::getMessage()->render('upload_shortcode');
 *	// OR copy them to the default message namespace
 *	e107::getMessage()->moveStack('upload_shortcode', 'default');
 *	// Do something... and render all messages
 *	echo e107::getMessage()->render();
 *	<code>
 * @return mixed Based on 'return_type' parameter - string or uploaded array result
 */
function uploadfile_shortcode($parm)
{
	if(!FILE_UPLOADS)
	{
		return LAN_UPLOAD_SERVEROFF;
	}
	if(USER_AREA === TRUE && !check_class(e107::getPref('upload_class')))
	{
		return LAN_DISABLED;
	}

	$parm = explode('|', $parm, 2);

	$path = $parm[0];
	if($path && !is_writable($path))
	{
		return LAN_UPLOAD_777." <b>".str_replace("../","",$path)."</b>";
	}

	$parms = array();
	parse_str(varset($parm[1], ''), $parms);

	$parms = array_merge(array(
		'trigger'		=> 'uploadfiles',
		'name'			=> 'file_userfile',
		'up_container' 	=> 'up_container',
		'up_row' 		=> 'upline',
		'process' 		=> '0',
		'upload_file_mask' 	=> '',
		'upload_filetypes' 	=> '',
		'upload_extra_file_types' => '0',
		'upload_final_chmod' => '',
		'upload_max_upload_size' => '0',
		'upload_max_file_count' => '0',
		'upload_overwrite'	=> '0',
		'return_type'	=> 'message',
	), $parms);

	// PROCESS UPLOADED FILES
	if($parms['process'])
	{
		e107_require_once(e_HANDLER.'upload_handler.php');
		$options = array(
			'file_mask' => $parms['upload_file_mask'],
			'filetypes' => $parms['upload_filetypes'],
			'extra_file_types' => $parms['upload_extra_file_types'] ? true : false,
			'final_chmod' => $parms['upload_final_chmod'] ? intval(intval($parms['upload_final_chmod']), 8) : 0644,
			'max_upload_size' => $parms['upload_max_upload_size'],
			'file_array_name' => $parms['name'],
			'max_file_count' => $parms['upload_max_file_count'],
			'overwrite' => $parms['upload_overwrite'] ? true : false,
		);

		$uploaded = process_uploaded_files($path, false, $options);
		if($uploaded)
		{
			$emessage = e107::getMessage();
			foreach ($uploaded as $finfo)
			{
				$emessage->addStack($finfo['message'], 'upload_shortcode', $finfo['error'] ? E_MESSAGE_ERROR : E_MESSAGE_SUCCESS);
			}
			return($parms['return_type'] == 'message' ? $emessage->render('upload_shortcode') : '');
		}
		return($parms['return_type'] == 'result' ? $uploaded : '');
	}

	// RENDER FORM
	$onclickt = !isset($parms['nowarn']) ? " onclick=\"return jsconfirm('".LAN_UPLOAD_CONFIRM."')\"" : '';
	$onclickd = " onclick=\"duplicateHTML('{$parms['up_row']}','{$parms['up_container']}');\"";
	$name = $parms['name'].'[]';

	$text .="
	        <!-- Upload Shortcode -->
			<div>
				<div class='field-spacer'>
					<button class='action duplicate' type='button' value='no-value'{$onclickd}><span>".LAN_UPLOAD_ADDFILE."</span></button>
					<button class='upload' type='submit' name='{$parms['trigger']}' value='no-value'{$onclickt}><span>".LAN_UPLOAD_FILES."</span></button>
				</div>
				<div id='{$parms['up_container']}'>
					<div id='{$parms['up_row']}' class='nowrap'>
						<input class='tbox file' type='file' name='{$name}' />
			        </div>
				</div>
			</div>
			<!-- End Upload Shortcode -->
		";

	return $text;
}