<?php
/**
 *
 * PM Rules extension for the phpBB Forum Software package
 *
 * @copyright (c) 2021, Kailey Snay, https://www.snayhomelab.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace kaileymsnay\pmrules\migrations\v10x;

class m1_initial_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('pm_post_limit');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	/**
	 * Add, update or delete data stored in the database
	 */
	public function update_data()
	{
		return [
			// Add config table settings
			['config.add', ['pm_post_limit', 5]],
		];
	}
}
