<?php
/**
 *
 * PM Rules extension for the phpBB Forum Software package
 *
 * @copyright (c) 2021, Kailey Snay, https://www.snayhomelab.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kaileymsnay\pmrules\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * PM Rules event listener
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var */
	protected $root_path;

	/** @var */
	protected $php_ext;

	/** @var */
	protected $tables;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth                   $auth
	 * @param \phpbb\config\config               $config
	 * @param \phpbb\db\driver\driver_interface  $db
	 * @param \phpbb\language\language           $language
	 * @param \phpbb\user                        $user
	 * @param                                    $root_path
	 * @param                                    $php_ext
	 * @param                                    $tables
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\user $user, $root_path, $php_ext, $tables)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->tables = $tables;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'	=> 'user_setup',

			'core.acp_board_config_edit_add'	=> 'acp_board_config_edit_add',

			'core.message_list_actions'	=> 'message_list_actions',
		];
	}

	public function user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'kaileymsnay/pmrules',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function acp_board_config_edit_add($event)
	{
		if ($event['mode'] == 'message')
		{
			$config_vars = [
				'pm_post_limit'	=> ['lang' => 'PM_POST_LIMIT', 'validate' => 'int:0:9999', 'type' => 'number:0:9999', 'explain' => true],
			];

			$event->update_subarray('display_vars', 'vars', phpbb_insert_config_array($event['display_vars']['vars'], $config_vars, ['after' => 'pm_max_recipients']));
		}
	}

	public function message_list_actions($event)
	{
		$address_list = $event['address_list'];
		$error = $event['error'];

		$allowed = $this->pm_posts_check();

		// Grab an array of user_id's with admin and mod permissions
		$admin = $this->auth->acl_get_list(false, 'a_', false);
		$admin = (!empty($admin[0]['a_'])) ? $admin[0]['a_'] : [];

		$mod = $this->auth->acl_get_list(false, 'm_', false);
		$mod = (!empty($mod[0]['m_'])) ? $mod[0]['m_'] : [];

		$team = array_unique(array_merge($admin, $mod));

		if (!$allowed && $address_list)
		{
			$sql = 'SELECT user_id
				FROM ' . $this->tables['users'] . '
				WHERE ' . $this->db->sql_in_set('user_id', array_keys($address_list['u'])) . '
					AND user_id <> ' . $this->user->data['user_id'];
			$result = $this->db->sql_query($sql);
			$removed = false;
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!in_array($row['user_id'], $team))
				{
					$removed = true;
					unset($address_list['u'][$row['user_id']]);
				}
			}
			$this->db->sql_freeresult($result);

			// Print a notice telling the user that they may only PM team members
			if ($removed)
			{
				$error[] = $this->language->lang('PM_TEAM_MEMBERS', append_sid($this->root_path . 'memberlist.' . $this->php_ext, 'mode=leaders'));
			}
		}

		$event['address_list'] = $address_list;
		$event['error'] = $error;
	}

	private function pm_posts_check()
	{
		// This is only for registered users
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			return;
		}

		// Default is true
		$allowed = true;

		// Don't check team members
		if ($this->auth->acl_gets('a_', 'm_') || $this->auth->acl_getf_global('m_'))
		{
			return $allowed;
		}

		if ($this->user->data['user_posts'] < $this->config['pm_post_limit'])
		{
			$allowed = false;
		}

		return $allowed;
	}
}
