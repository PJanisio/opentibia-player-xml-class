<?php
/*
Open Tibia XML player class
Version: 1.1.1
Author: Pawel 'Pavlus' Janisio
License: MIT
Github: https://github.com/PJanisio/opentibia-player-xml-class
*/



class xmlPlayer
{

	//predefined variables
	//private
	private $dataPath = '';
	private $realPath = '';
	private $housesPath = '';
	private $mapPath = '';
	private $monsterPath = '';
	private $guildPath = '';
	private $showError = 1; //shows backtrace of error message //def: 1
	//public
	//strings
	public $errorTxt = ''; //placeholder for error text //def: ''
	public $playerName = '';
	public $skull = '';
	public $playersDir = '';
	public $accountsDir = '';
	public $lastElement = ''; //double check if will be needed
	public $xmlPlayerFilePath = ''; //exact path for PREPARED player
	public $xmlAccountFilePath = ''; //exact path for PREPARED account
	public $structurePlayer = '';
	public $structureAccount = '';
	public $vocationName = '';
	public $outfitUrl = '';
	//bools
	public $xmlPlayer = NULL; //handler for player
	public $xmlAccount = NULL; //handler for account
	//ints and floats
	public $account = 0;
	public $food = 0;
	public $reqMana = 0;
	public $magicLevelPercent = 0;
	public $expNextLevel = 0;
	public $expPercNextLevel = 0;
	public $expLevel = 0;
	public $age = 0;
	//arrays
	public $skills = array();
	public $look = array();
	public $characters = array(); //names of other players on the same account
	public $spawn = array();
	public $temple = array();
	public $frags = array();
	public $lastModified = array();
	public $health = array();
	public $mana = array();
	public $storage = array();
	public $ban = array(); //ban status,start,end,comment
	public $dead = array();
	public $house = array();
	public $kills = array();
	public $boost = array();
	public $playerGuilds = array();
	public $slotsData = array();

	/*
Checks paths and define directories
*/
	public function __construct($dataPath)
	{

		$this->dataPath = $dataPath;
		$this->realPath = realpath($this->dataPath);

		//check if this is real path and directory
		if ($this->realPath == false or !is_dir($this->realPath)) {
			$this->throwError('Data path invalid!', 1);
		}

		//check if there exists player anc accounts directory	
		if (!is_dir($this->realPath . '/players') or !is_dir($this->realPath . '/accounts')) {
			$this->throwError('Players/Accounts path is invalid!', 1);
		} else {
			$this->playersDir = $this->realPath . '/players/';
			$this->accountsDir = $this->realPath . '/accounts/';
			$this->housesPath = $this->realPath . '/houses/';
			$this->mapPath = $this->realPath . '/world/';
			$this->monsterPath = $this->realPath . '/monster/';
			$this->guildPath = $this->realPath . '/guilds.xml';
		}
	}

	/*
Throwing error function
*/
	public function throwError($errorTxt, $showError = 1)
	{
		$this->errorTxt = $errorTxt;
		if ($showError == 1) {
			throw new Exception($this->errorTxt);
		}
	}

	/*
Check if its player (if false than other creature)
*/

	public function isPlayer($playerName)
	{
		// Construct the full path to the player file
		$filePath = $this->playersDir . $playerName . '.xml';

		// Check if the file exists
		return file_exists($filePath);
	}

	/*
Opens xml stream for player and account file
*/

	public function prepare($playerName)
	{
		//function to open xml stream

		$playerName = trim(stripslashes($playerName));
		$this->xmlPlayerFilePath = $this->playersDir . $playerName . '.xml';

		$this->xmlPlayer = simplexml_load_file($this->xmlPlayerFilePath, 'SimpleXMLElement', LIBXML_PARSEHUGE);

		if ($this->xmlPlayer === FALSE) //returns not boolean false what the heck
			$this->throwError('Player do not exists!', 1);
		else {
			$this->xmlAccountFilePath = $this->accountsDir . $this->getAccount() . '.xml';
			$this->xmlAccount = simplexml_load_file($this->xmlAccountFilePath, 'SimpleXMLElement', LIBXML_PARSEHUGE);

			if ($this->xmlAccount === FALSE)
				$this->throwError('Account file for player do not exists!', 1);
		}
		if ($this->xmlAccount and $this->xmlPlayer)
			return TRUE;

		//no need to close the file manually, will be auto-closed after reading content!
	}

	/*
Get functions
*/


	/*
Show xml structure for player file
*/
	public function showStructurePlayer()
	{
		echo '<pre>', var_dump($this->xmlPlayer), '</pre>';
	}

	/*
Show xml structure for account file
*/
	public function showStructureAccount()
	{
		echo '<pre>', var_dump($this->xmlAccount), '</pre>';
	}


	/*
Show last modyfied player files (by save or by class action)
*/
	public function showLastModifiedPlayers($minutes, $dateFormat = NULL)
	{


		if (!isset($dateFormat))
			$dateFormat = 'Y-m-d H:i:s';

		$files = scandir($this->playersDir);
		foreach ($files as $file) {
			$stat = stat($this->playersDir . $file);

			$lastmod = $stat['mtime'];
			$now = time();

			if ($now - $lastmod < $minutes * 60)
				$this->lastModified[$file] = date($dateFormat, $lastmod);
		}

		return $this->lastModified;
	}


	/*
Get account number/name
*/
	public function getAccount()
	{

		return strval($this->xmlPlayer['account']);
	}


	/*
Get premium days
*/
	public function getPremDays()
	{

		return intval($this->xmlAccount['premDays']);
	}

	/*
Get other characters on the same account
*/
	public function getCharacters()
	{

		for ($k = 0; $k < count($this->xmlAccount->characters->character); $k++) {
			$character = $this->xmlAccount->characters->character[$k]['name'];
			array_push($this->characters, $character);
		}

		return $this->characters; //array of objects

	}

	/*
Get sex
enum playersex_t {
	PLAYERSEX_FEMALE = 0,
	PLAYERSEX_MALE = 1,
	PLAYERSEX_OLDMALE = 2,
	PLAYERSEX_DWARF = 3,
	PLAYERSEX_NIMFA = 4, 
};
*/
	public function getSex()
	{

		return intval($this->xmlPlayer['sex']);
	}


	/*
Get looktype and look direction
*/
	public function getLookType()
	{

		$this->look['lookdir'] = intval($this->xmlPlayer['lookdir']);
		$this->look['type'] = intval($this->xmlPlayer->look['type']);
		$this->look['head'] = intval($this->xmlPlayer->look['head']);
		$this->look['body'] = intval($this->xmlPlayer->look['body']);
		$this->look['legs'] = intval($this->xmlPlayer->look['legs']);
		$this->look['feet'] = intval($this->xmlPlayer->look['feet']);

		return $this->look;
	}


	/*
Get experience points
*/
	public function getExp()
	{

		return intval($this->xmlPlayer['exp']);
	}


	/*
Get experience for any level
specialDivider works when custom formula is used to calculate experience
*/

	public function getExpForLevel($level, $specialDivider = 1)
	{

		$this->expLevel = $this->expLevel = ((((50 * $level / 3 - 100) * $level + 850 / 3) * $level - 200) / $specialDivider);

		return intval($this->expLevel);
	}

	/*
Get experience for player next level
*/

	public function getExpForNextLevel($specialDivider = 1)
	{

		$currentExp = $this->getExp();
		$nextLevel = $this->getLevel() +  1;

		//get exp for next level
		$this->expNextLevel = ((((50 * $nextLevel / 3 - 100) * $nextLevel + 850 / 3) * $nextLevel - 200) / $specialDivider) - $currentExp;

		return intval($this->expNextLevel);
	}



	/*
Get percentage value for next level as float
*/

	public function getExpPercentNextLevel($specialDivider = 1)
	{

		$currentLevelExp = $this->getExpForLevel($this->getLevel(), $specialDivider);
		$nextLevelExp = $this->getExpForLevel($this->getLevel() + 1, $specialDivider);
		$expForNextLvl = $this->getExpForNextLevel($specialDivider);

		$this->expPercNextLevel = round(($expForNextLvl / ($nextLevelExp - $currentLevelExp) * 100), 1);

		return floatval(abs($this->expPercNextLevel)); //return percent

	}

	/*
Get vocation
enum playervoc_t {
	VOCATION_NONE = 0,
	VOCATION_SORCERER = 1,
	VOCATION_DRUID = 2,
	VOCATION_PALADIN = 3,
	VOCATION_KNIGHT = 4
};
*/
	public function getVocation()
	{

		return intval($this->xmlPlayer['voc']);
	}

	/*
Get vocation name and check promotion
*/
	public function getVocationName()
	{

		$vocation = $this->getVocation();
		$promotion = $this->getPromotion();


		switch ([$vocation, $promotion]) {
			case [0, 0]:
				$this->vocationName = 'No vocation';
				break;

			case [1, 0]:
				$this->vocationName = 'Sorcerer';
				break;

			case [1, 1]:
				$this->vocationName = 'Master Sorcerer';
				break;

			case [2, 0]:
				$this->vocationName = 'Druid';
				break;

			case [2, 1]:
				$this->vocationName = 'Elder Druid';
				break;

			case [3, 0]:
				$this->vocationName = 'Paladin';
				break;

			case [3, 1]:
				$this->vocationName = 'Royal Paladin';
				break;

			case [4, 0]:
				$this->vocationName = 'Knight';
				break;

			case [4, 1]:
				$this->vocationName = 'Elite Knight';
				break;
		}

		return $this->vocationName;
	}


	/*
Get level
*/
	public function getLevel()
	{

		return intval($this->xmlPlayer['level']);
	}



	/*
Get skill tries
*/

	public function getReqSkillTries($skill, $level, $voc)
	{
		// Skill bases for each skill type
		$skillBases = [50, 50, 50, 50, 30, 100, 20];

		// Skill multipliers for each skill type and vocation
		$skillMultipliers = [
			[1.5, 1.5, 1.5, 1.2, 1.1], // Fist
			[2.0, 2.0, 1.8, 1.2, 1.1], // Club
			[2.0, 2.0, 1.8, 1.2, 1.1], // Sword
			[2.0, 2.0, 1.8, 1.2, 1.1], // Axe
			[2.0, 2.0, 1.8, 1.1, 1.4], // Distance
			[1.5, 1.5, 1.5, 1.1, 1.1], // Shielding
			[1.1, 1.1, 1.1, 1.1, 1.1]  // Fishing
		];

		// Calculate the required skill tries
		$reqSkillTries = $skillBases[$skill] * pow($skillMultipliers[$skill][$voc], $level - 11);

		return intval($reqSkillTries);
	}

	/*
Get skill percent for next level
*/

	public function getSkillPercentForNextLevel($skillId)
	{
		// Get the current skill level and tries from the XML
		$currentSkill = $this->xmlPlayer->skills->skill[$skillId];
		$currentLevel = intval($currentSkill['level']);
		$currentTries = intval($currentSkill['tries']);

		// Get the vocation of the player
		$voc = $this->getVocation();

		// Calculate the required skill tries for the next level
		$reqTriesNextLevel = $this->getReqSkillTries($skillId, $currentLevel + 1, $voc);

		// Calculate the percentage of progress towards the next level
		if ($reqTriesNextLevel == 0) {
			return 100; // Already at max level or no progress needed
		}

		$progress = ($currentTries / $reqTriesNextLevel) * 100;

		// Round the progress to the nearest whole number
		$roundedProgress = round(max(0, min(100, $progress)));

		return intval($roundedProgress); // Ensure it's an integer
	}


	/*
Get skill levels and skill percentage to next level
*/

	public function getSkills()
	{
		$this->skills['fist'] = intval($this->xmlPlayer->skills->skill[0]['level']);
		$this->skills['club'] = intval($this->xmlPlayer->skills->skill[1]['level']);
		$this->skills['sword'] = intval($this->xmlPlayer->skills->skill[2]['level']);
		$this->skills['axe'] = intval($this->xmlPlayer->skills->skill[3]['level']);
		$this->skills['distance'] = intval($this->xmlPlayer->skills->skill[4]['level']);
		$this->skills['shield'] = intval($this->xmlPlayer->skills->skill[5]['level']);

		// Add skill percentages for next level
		$this->skills['fist_percent'] = $this->getSkillPercentForNextLevel(0);
		$this->skills['club_percent'] = $this->getSkillPercentForNextLevel(1);
		$this->skills['sword_percent'] = $this->getSkillPercentForNextLevel(2);
		$this->skills['axe_percent'] = $this->getSkillPercentForNextLevel(3);
		$this->skills['distance_percent'] = $this->getSkillPercentForNextLevel(4);
		$this->skills['shield_percent'] = $this->getSkillPercentForNextLevel(5);

		return $this->skills; // Returns an associative array
	}


	/*
Get access
*/
	public function getAccess()
	{

		return intval($this->xmlPlayer['access']);
	}


	/*
Get capacity
*/
	public function getCapacity()
	{

		return intval($this->xmlPlayer['cap']);
	}


	/*
Get bless level
*not standard
*/
	public function getBless()
	{

		return intval($this->xmlPlayer['bless']);
	}

	/*
Get magiclevel
*/
	public function getMagicLevel()
	{

		return intval($this->xmlPlayer['maglevel']);
	}


	/*
Get lastlogin
Available formats at: http://php.net/manual/en/function.date.php
F.e: Y-m-d H:i:s
*/
	public function getLastLogin($format = NULL)
	{

		$time = intval($this->xmlPlayer['lastlogin']);

		if ($format != NULL)
			return date($format, $time);
		else
			return intval($time);
	}

	/*
Get promoted status
*/
	public function getPromotion()
	{

		return intval($this->xmlPlayer['promoted']);
	}


	/*
Get ban status
*/
	public function getBanStatus()
	{

		$this->ban['status'] = intval($this->xmlPlayer->ban['banned']); //0;1
		$this->ban['start'] = intval($this->xmlPlayer->ban['banstart']); //timestamp
		$this->ban['end'] = intval($this->xmlPlayer->ban['banend']); //timestamp
		$this->ban['comment'] = strval($this->xmlPlayer->ban['comment']);
		$this->ban['action'] = strval($this->xmlPlayer->ban['action']);
		$this->ban['reason'] = strval($this->xmlPlayer->ban['reason']);
		$this->ban['banrealtime'] = strval($this->xmlPlayer->ban['banrealtime']);
		$this->ban['deleted'] = intval($this->xmlPlayer->ban['deleted']); //0;1
		$this->ban['finalwarning'] = intval($this->xmlPlayer->ban['finalwarning']); //0;1

		return $this->ban;
	}


	/*
Get spawn position as an array
*/
	public function getSpawnCoordinates()
	{

		$this->spawn['x'] = intval($this->xmlPlayer->spawn['x']);
		$this->spawn['y'] = intval($this->xmlPlayer->spawn['y']);
		$this->spawn['z'] = intval($this->xmlPlayer->spawn['z']);

		return $this->spawn;
	}

	/*
Get temple position as an array
*/
	public function getTempleCoordinates()
	{

		$this->temple['x'] = intval($this->xmlPlayer->temple['x']);
		$this->temple['y'] = intval($this->xmlPlayer->temple['y']);
		$this->temple['z'] = intval($this->xmlPlayer->temple['z']);

		return $this->temple;
	}

	/*
Get skull type
	SKULL_NONE = 0,
	SKULL_YELLOW = 1,
	SKULL_WHITE = 3,
	SKULL_RED = 4
*/
	public function getSkull()
	{

		$this->skull = $this->xmlPlayer->skull['type'];

		switch ($this->skull) {
			case 1:
				return $this->skull = 'YELLOW_SKULL';
			case 3:
				return $this->skull = 'WHITE_SKULL';
			case 4:
				return $this->skull = 'RED_SKULL';
			default:
				return $this->skull = 'NO_SKULL';
		}
	}

	/*
Get frags as an array
*/
	public function getFrags()
	{

		$this->frags['kills'] = intval($this->xmlPlayer->skull['kills']); //int
		$this->frags['ticks'] = intval($this->xmlPlayer->skull['ticks']);
		$this->frags['absolve'] = intval($this->xmlPlayer->skull['absolve']);

		return $this->frags; //array

	}


	/*
Get health
now
max
*/
	public function getHealth()
	{

		$this->health['now'] = intval($this->xmlPlayer->health['now']);
		$this->health['max'] = intval($this->xmlPlayer->health['max']);

		return $this->health; //array

	}


	/*
Get food level
food maximum level = 1200000 (?)
food > 1000 - gaining health and mana
*/
	public function getFoodLevel()
	{

		$this->food = intval($this->xmlPlayer->health['food']);

		return $this->food;
	}


	/*
Get mana information
*/
	public function getMana()
	{

		$this->mana['now'] = intval($this->xmlPlayer->mana['now']);
		$this->mana['max'] = intval($this->xmlPlayer->mana['max']);
		$this->mana['spent'] = intval($this->xmlPlayer->mana['spent']);

		return $this->mana;
	}

	/*
Get required mana level
cpp source -> unsigned int Player::getReqMana(int maglevel, playervoc_t voc)
not tested yet :)
*/
	public function getRequiredMana($mlevel = NULL)
	{

		//use mana spent and formula
		$vocationMultiplayer = array(1, 1.1, 1.1, 1.4, 3);

		if (!isset($mlevel))
			$mlevel = $this->getMagicLevel();

		$this->reqMana = intval((400 * pow($vocationMultiplayer[$this->getVocation()], $mlevel - 1)));

		if ($this->reqMana % 20 < 10) //CIP must have been bored when they invented this odd rounding
			$this->reqMana = $this->reqMana - ($this->reqMana % 20);
		else
			$this->reqMana = $this->reqMana - ($this->reqMana % 20) + 20;

		return intval($this->reqMana);
	}

	/*
Get percentage magic level
cpp source -> void Player::sendStats()
*/
	public function getMagicLevelPercent()
	{

		$this->getMana();
		$this->magicLevelPercent = intval(100 * ($this->mana['spent'] / (1. * $this->getRequiredMana($this->getMagicLevel() + 1))));

		return intval($this->magicLevelPercent);
	}


	/*
Get houses players own or is invited
this method doesnt need to use prepare for the player file
*/
	public function getHouses($playerName)
	{

		$houseFound = array(); //start array where player is stored

		$houses = glob($this->housesPath . '*.xml');

		// Make sure we sanitize the player name for a proper match
		$playerName = trim(strtolower($playerName)); // Convert to lowercase for case-insensitive comparison

		foreach ($houses as $house) {
			// Open the house XML
			$xml = simplexml_load_file($house);

			// Check each ownership, subowner, doorowner, and guest tag for an exact match of the player's name
			foreach ($xml->owner as $owner) {
				if (strtolower($owner['name']) == $playerName) {  // Use strtolower for case-insensitive comparison
					$houseFound[] = $house;
				}
			}

			foreach ($xml->subowner as $subowner) {
				if (strtolower($subowner['name']) == $playerName) {
					$houseFound[] = $house;
				}
			}

			foreach ($xml->doorowner as $doorowner) {
				if (strtolower($doorowner['name']) == $playerName) {
					$houseFound[] = $house;
				}
			}

			foreach ($xml->guest as $guest) {
				if (strtolower($guest['name']) == $playerName) {
					$houseFound[] = $house;
				}
			}
		}

		// Count and assign the house ownership/subownership/guest status
		$this->house['count'] = count($houseFound);
		$this->house['owner'] = '';
		$this->house['subowner'] = '';
		$this->house['doorowner'] = '';
		$this->house['guest'] = '';

		foreach ($houseFound as $playerHouse) {
			$xml = simplexml_load_file($playerHouse);

			// Check ownership
			foreach ($xml->owner as $owner) {
				if (strtolower($owner['name']) == $playerName) {
					$this->house['owner'] .= basename($playerHouse, '.xml') . ', ';
				}
			}

			// Check subownership
			foreach ($xml->subowner as $subowner) {
				if (strtolower($subowner['name']) == $playerName) {
					$this->house['subowner'] .= basename($playerHouse, '.xml') . ', ';
				}
			}

			// Check door ownership
			foreach ($xml->doorowner as $doorowner) {
				if (strtolower($doorowner['name']) == $playerName) {
					$this->house['doorowner'] .= basename($playerHouse, '.xml') . ', ';
				}
			}

			// Check guest status
			foreach ($xml->guest as $guest) {
				if (strtolower($guest['name']) == $playerName) {
					$this->house['guest'] .= basename($playerHouse, '.xml') . ', ';
				}
			}
		}

		return $this->house; // Return array of houses and rights
	}


	/*
Get storage values
*/

	public function getStorageValues()
	{

		foreach ($this->xmlPlayer->storage->data as $item) {

			$key = strval($item['key']);
			$value = strval($item['value']);
			$this->storage[$key] = $value;
		}

		return $this->storage; //array

	}

	/*
Get deaths
*/
	public function getDeaths()
	{


		foreach ($this->xmlPlayer->deaths->death as $id) {
			$this->dead[] = $id;
		}

		return $this->dead; //array of objects

	}


	/*
Get player age (in seconds)
*/
	public function getAge()
	{
		$this->age = intval($this->xmlPlayer['age']);
		return $this->age;
	}

	/*
Get kills statistics from the <skull> node:
totalKills, totalDeaths, nsKills, wsKills, ysKills, rsKills
*/
public function getKills()
{
    // Re-initialize $this->kills
    $this->kills = [];

    $this->kills['totalKills']   = isset($this->xmlPlayer->skull['totalKills']) 
        ? intval($this->xmlPlayer->skull['totalKills']) 
        : 0;

    $this->kills['totalDeaths']  = isset($this->xmlPlayer->skull['totalDeaths']) 
        ? intval($this->xmlPlayer->skull['totalDeaths']) 
        : 0;

    $this->kills['nsKills']      = isset($this->xmlPlayer->skull['nsKills']) 
        ? intval($this->xmlPlayer->skull['nsKills']) 
        : 0;

    $this->kills['wsKills']      = isset($this->xmlPlayer->skull['wsKills']) 
        ? intval($this->xmlPlayer->skull['wsKills']) 
        : 0;

    $this->kills['ysKills']      = isset($this->xmlPlayer->skull['ysKills']) 
        ? intval($this->xmlPlayer->skull['ysKills']) 
        : 0;

    $this->kills['rsKills']      = isset($this->xmlPlayer->skull['rsKills']) 
        ? intval($this->xmlPlayer->skull['rsKills']) 
        : 0;

    return $this->kills;
}

	/*
Get boost status
status, ticks, task, timestamp, reroll
*/
public function getBoostStatus()
{
    // Re-initialize $this->boost
    $this->boost = [];

    $this->boost['status']    = isset($this->xmlPlayer->boost['status']) 
        ? intval($this->xmlPlayer->boost['status']) 
        : 0;

    $this->boost['ticks']     = isset($this->xmlPlayer->boost['ticks']) 
        ? intval($this->xmlPlayer->boost['ticks']) 
        : 0;

    $this->boost['task']      = isset($this->xmlPlayer->boost['task']) 
        ? intval($this->xmlPlayer->boost['task']) 
        : 0;

    $this->boost['timestamp'] = isset($this->xmlPlayer->boost['timestamp']) 
        ? intval($this->xmlPlayer->boost['timestamp']) 
        : 0;

    $this->boost['reroll']    = isset($this->xmlPlayer->boost['reroll']) 
        ? intval($this->xmlPlayer->boost['reroll']) 
        : 0;

    return $this->boost;
}


	/*
Create an outfit url (ots.me)
*/

	public function showOutfit()
	{

		$look = $this->getLookType();

		$this->outfitUrl = 'https://outfit-images.ots.me/772/animoutfit.php?id=' . $look['type'] . '&addons=1&head=' . $look['head'] . '&body=' . $look['body'] . '&legs=' . $look['legs'] . '&feet=' . $look['feet'] . '&mount=0&direction=3';

		return $this->outfitUrl;
	}

	/*
Get guild name and member status
*/

	public function getGuild()
	{
		// If there's no guilds.xml file, we can throw an error or return an empty array.
		if (!file_exists($this->guildPath)) {
			$this->throwError('Guilds file not found!', 1);
			return array();
		}

		// Load the guild XML
		$guildsXml = simplexml_load_file($this->guildPath, 'SimpleXMLElement', LIBXML_PARSEHUGE);
		if ($guildsXml === false) {
			$this->throwError('Could not parse guilds.xml!', 1);
			return array();
		}


		$playerName = strval($this->xmlPlayer['name']);

		$playerGuilds = array();

		foreach ($guildsXml->guild as $guildNode) {
			foreach ($guildNode->member as $member) {
				if (strval($member['name']) === $playerName) {

					$statusInt = intval($member['status']);

					// enum gstat_t {
					//   GUILD_NONE,    // 0
					//   GUILD_INVITED, // 1
					//   GUILD_MEMBER,  // 2
					//   GUILD_VICE,    // 3
					//   GUILD_LEADER   // 4
					// };
					$statusName = 'GUILD_NONE';
					switch ($statusInt) {
						case 0:
							$statusName = 'GUILD_INVITED';
							break;
						case 1:
							$statusName = 'GUILD_MEMBER';
							break;
						case 2:
							$statusName = 'GUILD_VICE';
							break;
						case 4:
							$statusName = 'GUILD_LEADER';
							break;
					}

					// Guild name
					$guildName = strval($guildNode['name']);

					// Add this guild membership info to our result array
					$playerGuilds[] = array(
						'guildName' => $guildName,
						'guildStatus' => $statusName,
						'guildStatusId' => $statusInt
					);
				}
			}
		}

		return $playerGuilds;
	}

	/*
Get account points (zrzutkaPoints)
*/

	public function getPoints()
	{
		return intval($this->xmlAccount['zrzutkaPoints']);
	}

	/*
Get account email
*/

	public function getEmail()
	{
		return strval($this->xmlAccount['email']);
	}

		/*
Get items id in player slots (eq)
*/


	public function getEquipment()
{
    // Map your enum numeric values to their respective names:
    $slotNames = [
        0  => 'SLOT_WHEREEVER',
        1  => 'SLOT_HEAD',
        2  => 'SLOT_NECKLACE',
        3  => 'SLOT_BACKPACK',
        4  => 'SLOT_ARMOR',
        5  => 'SLOT_RIGHT',
        6  => 'SLOT_LEFT',
        7  => 'SLOT_LEGS',
        8  => 'SLOT_FEET',
        9  => 'SLOT_RING',
        10 => 'SLOT_AMMO',
        11 => 'SLOT_DEPOT'
    ];

    $this->slotsData = []; 

	if (!isset($this->xmlPlayer->inventory->slot)) {
        return $this->slotsData;
    }

    foreach ($this->xmlPlayer->inventory->slot as $slot) {
        $slotId = (int)$slot['slotid'];
        $itemId = isset($slot->item) ? (int)$slot->item['id'] : 0;

        $this->slotsData[$slotId] = [
            'slotName' => isset($slotNames[$slotId]) ? $slotNames[$slotId] : 'UNKNOWN_SLOT',
            'itemId'   => $itemId
        ];
    }

    return $this->slotsData;
}




/*
===========================================================	
Set functions
===========================================================	
*/


	/*
Set new password
*/

	public function setPassword($password)
	{

		$this->xmlAccount['pass'] = $password;
		$makeChange = $this->xmlAccount->asXML($this->xmlAccountFilePath);

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/*
Set new password
*/

	public function setPremDays($count)
	{

		$this->xmlAccount['premDays'] = $count;
		$makeChange = $this->xmlAccount->asXML($this->xmlAccountFilePath);

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/*
Set sex
*/

	public function setSex($number)
	{

		if ($number >= 0 and $number < 5) {

			$this->xmlPlayer['sex'] = $number;
			$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);
		} else {
			$this->throwError('Error: Range of arguments allowed: 0-4', 1);
		}

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/*
Remove character from account and delete player file
set second argument to TRUE if you want to remove account file altogether
*/

	public function removeCharacter($charName, $accountRemove = NULL)
	{

		foreach ($this->xmlAccount->characters->character as $seg) {

			if ($seg['name'] == $charName) {
				//remove child attribute from account file
				$dom = dom_import_simplexml($seg);
				$dom->parentNode->removeChild($dom);
				$makeChange = $this->xmlAccount->asXML($this->xmlAccountFilePath);
				//remove player file
				$makeRemove = unlink($this->xmlPlayerFilePath);
				if ($accountRemove == TRUE) {
					$makeRemoveAcc = unlink($this->xmlAccountFilePath);
				}
			} else {
				$this->throwError('Error: Player doesn`t exists.', 1);
			}
		}
		if (isset($makeChange) and isset($makeRemove) and isset($makeRemoveAcc)) {

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/*
Ban player
Args:
duration: set in houres
reason: will be displayed on site
*/


	public function setBan($duration, $reason, $comment, $finalwarning, $deleted, $extend = NULL)
	{

		$this->getBanStatus();
		if ($this->ban['status'] == 1 and $extend == NULL) {

			$this->throwError('Error: Player is already banned.', 1);
		} else {
			//check if player has already finalwarning if so, put deleted
			if ($this->ban['finalwarning'] == 1) {

				$deleted = 1;
			}

			$durationHoures = $duration * 3600;

			$this->xmlPlayer->ban['banned'] = 1; //0;1
			$this->xmlPlayer->ban['banstart'] = time(); //timestamp
			$this->xmlPlayer->ban['banend'] = time() + $durationHoures; //timestamp
			$this->xmlPlayer->ban['banrealtime'] = date('Y-m-d H:i:s', $this->ban['end']);
			$this->xmlPlayer->ban['comment'] = $comment;
			$this->xmlPlayer->ban['action'] = 'Account ban - XML class';
			$this->xmlPlayer->ban['reason'] = $reason;
			$this->xmlPlayer->ban['deleted'] = $deleted; //0;1
			$this->xmlPlayer->ban['finalwarning'] = $finalwarning; //0;1

			$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);
		}

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/*
Unban player
Optional args:
removeFW - removing final warning
removeDel - removing perm ban
*/

	public function removeBan($removeFW = NULL, $removeDel = NULL)
	{

		$this->getBanStatus();
		if ($this->ban['status'] == 0) {

			$this->throwError('Error: Player is not banned. Dont need any action', 1);
		} else {


			$this->xmlPlayer->ban['banned'] = 0; //0;1
			$this->xmlPlayer->ban['banstart'] = 0; //timestamp
			$this->xmlPlayer->ban['banend'] = 0; //timestamp
			//we do not clear banrealtime to get information when last ban happened
			//$this->xmlPlayer->ban['banrealtime'] = '';
			$this->xmlPlayer->ban['comment'] = '';
			$this->xmlPlayer->ban['action'] = '';
			$this->xmlPlayer->ban['reason'] = '';

			if ($removeFW == 1) {
				$this->xmlPlayer->ban['finalwarning'] = 0; //0;1
			}
			if ($removeDel == 1) {
				$this->xmlPlayer->ban['deleted'] = 0; //0;1
			}
			$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);
		}

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}




	/*
Set access
*/

	public function setAccess($number)
	{

		$this->xmlPlayer['access'] = intval($number);
		$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}



	/*
Set promotion
*/
	public function setPromotion($number)
	{

		$this->xmlPlayer['promoted'] = intval($number);
		$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}


	/*
Set capacity
*/
	public function setCapacity($number)
	{

		$this->xmlPlayer['cap'] = intval($number);
		$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);

		if ($makeChange) {

			return TRUE;
		} else {
			return FALSE;
		}
	}



	/*
Change player name
you have to manually change in guilds and houses when otserv is online
*/
	public function setName($name)
	{

		//changing player file
		$currentName = $this->xmlPlayer['name'];

		$this->xmlPlayer['name'] = strval($name);
		$makeChange = $this->xmlPlayer->asXML($this->xmlPlayerFilePath);

		$rename = rename(
			$this->xmlPlayerFilePath,
			$this->playersDir . $name . '.xml'
		);
		//changing account file

		foreach ($this->xmlAccount->characters->character as $seg) {

			if ($seg['name'] == $currentName) {

				$seg['name'] = trim($name);
				$makeChangeAcc = $this->xmlAccount->asXML($this->xmlAccountFilePath);
			}
		}

		if ($makeChange and $makeChangeAcc) {

			return TRUE;
		} else {
			return FALSE;
		}
	}



	/*
Set new points value node: zrzutkaPoints
*/

	public function setPoints($points)
	{
		// Update the 'zrzutkaPoints' attribute
		$this->xmlAccount['zrzutkaPoints'] = intval($points);

		// Save changes to the account file
		$saveStatus = $this->xmlAccount->asXML($this->xmlAccountFilePath);

		return $saveStatus !== false;
	}

	/*
Set new email value
*/

	public function setEmail($email)
	{
		// Update the 'email' attribute
		$this->xmlAccount['email'] = $email;

		// Save changes to the account file
		$saveStatus = $this->xmlAccount->asXML($this->xmlAccountFilePath);

		return $saveStatus !== false;
	}





	/*
This method can not be used as game engine stores houses information in memory and will overwrite saved data
*/

	/*


public function removePlayersHouses($playerName) {

	$houseFound = array(); //start array where player is stored

	$houses = glob($this->housesPath.'*.xml');


		foreach($houses as $house) {
				//opens a file
				$open = htmlentities(file_get_contents($house));
				//check if player is found
				//var_dump($open);
				$found = strpos($open, $playerName);
				
				if($found > 0) {
					//add housename to array
					//we can use later to display houises name player owns
					$houseFound[] = $house; 
				}

			}
			//we need to define empty strings
			$this->house['count'] = count($houseFound);
			

				foreach($houseFound as $playerHouse) {
					//lets open each house and check access rights for player
					$xml = simplexml_load_file($playerHouse);
					//var_dump($xml);


					//now we need to iterate of each ownership tag and delete the node
					foreach($xml->owner as $owner) {

						if($owner['name'] == $playerName) {

							unset($xml->owner['name']);
						}
					}

					foreach($xml->subowner as $subowner) {
							
						if($subowner['name'] == $playerName) {

							unset($xml->subowner['name']);
						}
					}
					foreach($xml->doorowner as $doorowner) {

						if($doorowner['name'] == $playerName) {

							unset($xml->doorowner['name']);
						}
					}

					foreach($xml->guest as $guest) {

						if($guest['name'] == $playerName) {

							unset($xml->guest['name']);
						}
					}


					$makeChange = $xml->asXML($playerHouse);

			}

			
			if($makeChange) {
		
				return TRUE;
			}
				else {
					return FALSE;
				}
			
			}

*/




	//end class
}
