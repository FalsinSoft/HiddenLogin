<?php
/**
* @package      Hidden Login
* @copyright    Copyright (C) 2010 FalsinSoft. All rights reserved.
* @license      GNU/GPL
* @website      https://sites.google.com/site/falsinsoftjoomlaextensions/
* @email        falsinsoft@gmail.com
* 
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.application.module.helper' ); 
 
class plgSystemHiddenLogin extends JPlugin
{
	public function plgSystemHiddenLogin(&$subject, $config)  
	{
        parent::__construct($subject, $config);
    }
	
	public function onAfterDispatch()
	{
		$app =& JFactory::getApplication();
		$document =& JFactory::getDocument();
		
		if($app->isAdmin() || $app->getCfg('offline') || $document->getType() != 'html') return true;   

		if(JRequest::getVar('hidden') !== null)
		{
			$document->addScript(JURI::root(true).'/plugins/system/hiddenlogin/tinybox2/packed.js');
			$document->addStyleSheet(JURI::root(true).'/plugins/system/hiddenlogin/tinybox2/style.css');
			$document->addScriptDeclaration("window.addEvent('domready',function(){TINY.box.show({html:'".$this->getLoginHtmlCode()."'});});");
		}

		return true;	
	}
	
	private function getLoginHtmlCode()
	{
		$language =& JFactory::getLanguage();
		$module = JModuleHelper::getModule('mod_login');
		$params = new JRegistry();
		
		$language->load('mod_login');
		$params->loadString($module->params);
		$return = JPATH_SITE;
		
		$html = '<form action="'.JRoute::_('index.php', true, $params->get('usesecure')).'" method="post" id="login-form" >';		
		
		if($params->get('pretext')) 
		{
			$html .= '<div class="pretext"><p>'.$params->get('pretext').'</p></div>';
		}

		$html .= '<fieldset class="userdata">';
		
		$html .= '<p id="form-login-username">';
		$html .= '<label for="modlgn-username">'.JText::_('MOD_LOGIN_VALUE_USERNAME').'</label>';
		$html .= '<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />';
		$html .= '</p>';
	
		$html .= '<p id="form-login-password">';
		$html .= '<label for="modlgn-passwd">'.JText::_('JGLOBAL_PASSWORD').'</label>';
		$html .= '<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18"  />';
		$html .= '</p>';
	
		if(JPluginHelper::isEnabled('system', 'remember'))
		{
			$html .= '<p id="form-login-remember">';
			$html .= '<label for="modlgn-remember">'.JText::_('MOD_LOGIN_REMEMBER_ME').'</label>';
			$html .= '<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>';
			$html .= '</p>';
		}
			
		$html .= '<input type="submit" name="Submit" class="button" value="'.JText::_('JLOGIN').'" />';
		$html .= '<input type="hidden" name="option" value="com_users" />';
		$html .= '<input type="hidden" name="task" value="user.login" />';
		$html .= '<input type="hidden" name="return" value="'.$return.'" />';
		$html .= JHtml::_('form.token');
		
		$html .= '</fieldset>';
		
		$html .= '<ul>';
		$html .= '<li><a href="'.JRoute::_('index.php?option=com_users&view=reset').'">'.JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD').'</a></li>';
		$html .= '<li><a href="'.JRoute::_('index.php?option=com_users&view=remind').'">'.JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME').'</a></li>';
		$html .= '</ul>';
		
		if($params->get('posttext'))
		{
			$html .= '<div class="posttext"><p>'.$params->get('posttext').'</p></div>';
		}
		
		$html .= '</form>';
		
		return $html;
	}
}