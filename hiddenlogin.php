<?php
/**
* @package      Hidden Login
* @copyright    Copyright (C) 2010 FalsinSoft. All rights reserved.
* @license      GNU/GPL
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.application.module.helper' ); 
 
class plgSystemHiddenLogin extends JPlugin
{
	public function plgSystemHiddenLogin(&$subject, $config)  
	{
        parent::__construct($subject, $config);
		$this->loadLanguage();
    }
	
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$login_url_param = trim($this->params->get('login_url_param', ''));
		
		if($app->isAdmin() || $app->getCfg('offline') || $document->getType() != 'html' || strlen($login_url_param) == 0) return true;   

		if(JRequest::getVar($login_url_param) !== null)
		{
			$document->addScript(JURI::root(true).'/plugins/system/hiddenlogin/tinybox2/packed.js');
			$document->addStyleSheet(JURI::root(true).'/plugins/system/hiddenlogin/tinybox2/style.css');
			$document->addScriptDeclaration("window.addEvent('domready',function(){TINY.box.show({html:'".$this->getLoginHtmlCode()."'});});");
		}

		return true;	
	}
	
	private function getLoginHtmlCode()
	{	
		$user = JFactory::getUser();
		
		$html = '<div style="padding:10px;">';
		
		if($user->get('guest'))
		{
			$html .= '<form action="'.JRoute::_('index.php', true).'" method="post" >';
			
			$html .= '<p style="padding-bottom:15px;font-size:24px;margin:0;line-height:0;">'.JText::_('MOD_HIDDEN_LOGIN_TITLE').'</p>';
			
			$html .= '<fieldset  style="padding-bottom:15px;margin:0;">';
			
			$html .= '<p style="padding-top:10px;">';
			$html .= '<label style="padding-right:10px;">'.JText::_('MOD_HIDDEN_LOGIN_USERNAME').'</label>';
			$html .= '<input type="text" name="username" class="inputbox"  size="18" />';
			$html .= '</p>';
		
			$html .= '<p>';
			$html .= '<label style="padding-right:18px;">'.JText::_('JGLOBAL_PASSWORD').'</label>';
			$html .= '<input type="password" name="password" class="inputbox" size="18"  />';
			$html .= '</p>';
		
			if(JPluginHelper::isEnabled('system', 'remember'))
			{
				$html .= '<p style="padding-bottom:15px;">';
				$html .= '<label style="padding-right:10px;">'.JText::_('MOD_HIDDEN_LOGIN_REMEMBER_ME').'</label>';
				$html .= '<input type="checkbox" name="remember" class="inputbox" value="yes"/>';
				$html .= '</p>';
			}
				
			$html .= '<input type="submit" name="Submit" class="button" value="'.JText::_('JLOGIN').'" />';
			$html .= '<input type="hidden" name="option" value="com_users" />';
			$html .= '<input type="hidden" name="task" value="user.login" />';
			$html .= '<input type="hidden" name="return" value="'.$this->getReturnURL().'" />';
			$html .= JHtml::_('form.token');
			
			$html .= '</fieldset>';
			
			$html .= '<ul>';
			$html .= '<li style="padding-bottom:5px;"><a href="'.JRoute::_('index.php?option=com_users&view=reset').'">'.JText::_('MOD_HIDDEN_LOGIN_FORGOT_YOUR_PASSWORD').'</a></li>';
			$html .= '<li><a href="'.JRoute::_('index.php?option=com_users&view=remind').'">'.JText::_('MOD_HIDDEN_LOGIN_FORGOT_YOUR_USERNAME').'</a></li>';
			$html .= '</ul>';
					
			$html .= '</form>';
		}
		else
		{
			$html .= '<p>'.JText::_('MOD_HIDDEN_LOGIN_LOGGED').': <b>'.$user->get('name').'</b></p>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	private function getReturnURL()
	{
		$uri 	= clone JFactory::getURI();
		$app	= JFactory::getApplication();
		$router = $app->getRouter();
		$vars = $router->parse($uri);
		unset($vars['lang']);
		if ($router->getMode() == JROUTER_MODE_SEF)
		{
			if (isset($vars['Itemid']))
			{
				$itemid = $vars['Itemid'];
				$menu = $app->getMenu();
				$item = $menu->getItem($itemid);
				unset($vars['Itemid']);
				if (isset($item) && $vars == $item->query) {
					$url = 'index.php?Itemid='.$itemid;
				}
				else {
					$url = 'index.php?'.JURI::buildQuery($vars).'&Itemid='.$itemid;
				}
			}
			else
			{
				$url = 'index.php?'.JURI::buildQuery($vars);
			}
		}
		else
		{
			$url = 'index.php?'.JURI::buildQuery($vars);
		}
		
		return base64_encode($url);
	}
}