<?php 
namespace VanguardLTE\Games\NarcosNET
{
    class SlotSettings
    {
        public $playerId = null;
        public $splitScreen = null;
        public $reelStrip1 = null;
        public $reelStrip2 = null;
        public $reelStrip3 = null;
        public $reelStrip4 = null;
        public $reelStrip5 = null;
        public $reelStrip6 = null;
        public $reelStripBonus1 = null;
        public $reelStripBonus2 = null;
        public $reelStripBonus3 = null;
        public $reelStripBonus4 = null;
        public $reelStripBonus5 = null;
        public $reelStripBonus6 = null;
        public $slotId = '';
        public $slotDBId = '';
        public $Line = null;
        public $scaleMode = null;
        public $numFloat = null;
        public $gameLine = null;
        public $Bet = null;
        public $isBonusStart = null;
        public $Balance = null;
        public $SymbolGame = null;
        public $GambleType = null;
        public $lastEvent = null;
        public $Jackpots = [];
        public $keyController = null;
        public $slotViewState = null;
        public $hideButtons = null;
        public $slotReelsConfig = null;
        public $slotFreeCount = null;
        public $slotFreeMpl = null;
        public $slotWildMpl = null;
        public $slotExitUrl = null;
        public $slotBonus = null;
        public $slotBonusType = null;
        public $slotScatterType = null;
        public $slotGamble = null;
        public $Paytable = [];
        public $slotSounds = [];
        public $jpgs = null;
        private $Bank = null;
        private $Percent = null;
        private $WinLine = null;
        private $WinGamble = null;
        private $Bonus = null;
        private $shop_id = null;
        public $currency = null;
        public $user = null;
        public $game = null;
        public $shop = null;
        public $jpgPercentZero = false;
        public $count_balance = null;
        public $gameData = [];
        public $gameDataStatic = [];
        public function __construct($gameStateData)
        {
            $this->playerId = $gameStateData['playerId'];
            $this->user = (object) $gameStateData['user'];
            $this->game = (object) $gameStateData['game'];
            $this->shop = (object) $gameStateData['shop'];
            $this->Bank = $gameStateData['bank'];
            $this->Balance = $gameStateData['balance'];
            $this->Percent = $gameStateData['shop']['percent'] ?? 0; // Ensure 'shop' and 'percent' exist
            $this->gameData = $gameStateData['gameData'] ?? [];
            $this->currency = $gameStateData['currency'] ?? ''; // Add if not directly available via shop object
            $this->slotId = $gameStateData['game']['name'] ?? ''; // Assuming slotId is the game name
            $this->slotDBId = $gameStateData['game']['id'] ?? '';
            $this->MaxWin = $gameStateData['shop']['max_win'] ?? 0;
            $this->CurrentDenom = $gameStateData['game']['denomination'] ?? 1;
            $this->jpgs = isset($gameStateData['jpgs']) ? $gameStateData['jpgs'] : [];
            $this->shop_id = $gameStateData['user']['shop_id'] ?? 0;
            $this->count_balance = $gameStateData['user']['count_balance'] ?? 0;

            // Keep existing Paytable initialization
            $this->increaseRTP = 1;
            $this->scaleMode = 0;
            $this->numFloat = 0;
            $this->Paytable['SYM_0'] = [
                0, 
                0, 
                0, 
                0, 
                0, 
                0
            ];
            $this->Paytable['SYM_1'] = [
                0, 
                0, 
                0, 
                20, 
                80, 
                300
            ];
            $this->Paytable['SYM_2'] = [
                0, 
                0, 
                0, 
                0, 
                0, 
                0
            ];
            $this->Paytable['SYM_3'] = [
                0, 
                0, 
                0, 
                20, 
                80, 
                300
            ];
            $this->Paytable['SYM_4'] = [
                0, 
                0, 
                0, 
                20, 
                80, 
                300
            ];
            $this->Paytable['SYM_5'] = [
                0, 
                0, 
                0, 
                15, 
                60, 
                250
            ];
            $this->Paytable['SYM_6'] = [
                0, 
                0, 
                0, 
                15, 
                60, 
                250
            ];
            $this->Paytable['SYM_7'] = [
                0, 
                0, 
                0, 
                10, 
                30, 
                120
            ];
            $this->Paytable['SYM_8'] = [
                0, 
                0, 
                0, 
                10, 
                30, 
                120
            ];
            $this->Paytable['SYM_9'] = [
                0, 
                0, 
                0, 
                5, 
                15, 
                60
            ];
            $this->Paytable['SYM_10'] = [
                0, 
                0, 
                0, 
                5, 
                15, 
                60
            ];
            $this->Paytable['SYM_11'] = [
                0, 
                0, 
                0, 
                5, 
                10, 
                40
            ];
            $this->Paytable['SYM_12'] = [
                0, 
                0, 
                0, 
                5, 
                10, 
                40
            ];
            $reel = new GameReel();
            foreach( [
                'reelStrip1', 
                'reelStrip2', 
                'reelStrip3', 
                'reelStrip4', 
                'reelStrip5', 
                'reelStrip6'
            ] as $reelStrip ) 
            {
                if( count($reel->reelsStrip[$reelStrip]) ) 
                {
                    $this->$reelStrip = $reel->reelsStrip[$reelStrip];
                }
            }
            $this->keyController = [
                '13' => 'uiButtonSpin,uiButtonSkip', 
                '49' => 'uiButtonInfo', 
                '50' => 'uiButtonCollect', 
                '51' => 'uiButtonExit2', 
                '52' => 'uiButtonLinesMinus', 
                '53' => 'uiButtonLinesPlus', 
                '54' => 'uiButtonBetMinus', 
                '55' => 'uiButtonBetPlus', 
                '56' => 'uiButtonGamble', 
                '57' => 'uiButtonRed', 
                '48' => 'uiButtonBlack', 
                '189' => 'uiButtonAuto', 
                '187' => 'uiButtonSpin'
            ];
            $this->slotReelsConfig = [
                [
                    425, 
                    142, 
                    3
                ], 
                [
                    669, 
                    142, 
                    3
                ], 
                [
                    913, 
                    142, 
                    3
                ], 
                [
                    1157, 
                    142, 
                    3
                ], 
                [
                    1401, 
                    142, 
                    3
                ]
            ];
            $this->slotBonusType = 1;
            $this->slotScatterType = 0;
            $this->splitScreen = false;
            $this->slotBonus = true;
            $this->slotGamble = true;
            $this->slotFastStop = 1;
            $this->slotExitUrl = '/';
            $this->slotWildMpl = 1;
            $this->GambleType = 1;
            $this->Denominations = \VanguardLTE\Game::$values['denomination'];
            $this->CurrentDenom = $this->Denominations[0];
            $this->CurrentDenomination = $this->Denominations[0];
            $this->slotFreeCount = [
                0, 
                0, 
                0, 
                10, 
                10, 
                10
            ];
            $this->slotFreeMpl = 1;
            $this->slotViewState = ($this->game->slotViewState == '' ? 'Normal' : $this->game->slotViewState);
            $this->hideButtons = [];
            // $this->jpgs = \VanguardLTE\JPG::where('shop_id', $this->shop_id)->lockForUpdate()->get(); // Removed, initialized from $gameStateData
            $this->slotJackPercent = [];
            $this->slotJackpot = [];
            if(isset($this->game->jp_1)) { // Check if jp_1 exists, assuming if one exists, all exist
                for( $jp = 1; $jp <= 4; $jp++ )
                {
                    $this->slotJackpot[] = $this->game->{'jp_' . $jp};
                    $this->slotJackPercent[] = $this->game->{'jp_' . $jp . '_percent'};
                }
            }

            $this->Line = isset($gameStateData['game']['lines']) ? explode(',', $gameStateData['game']['lines']) : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
            $this->gameLine = isset($gameStateData['game']['gameLine']) ? explode(',', $gameStateData['game']['gameLine']) : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
            $this->Bet = isset($this->game->bet) ? explode(',', $this->game->bet) : [];
            // Balance is already initialized from $gameStateData
            $this->SymbolGame = isset($gameStateData['game']['SymbolGame']) ? $gameStateData['game']['SymbolGame'] : ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
            // Bank is already initialized from $gameStateData
            // Percent is already initialized from $gameStateData
            $this->WinGamble = $this->game->rezerv ?? 0;
            // slotDBId is already initialized from $gameStateData
            $this->slotCurrency = $this->shop->currency ?? '';
            // count_balance is already initialized from $gameStateData

            if( ($this->user->address ?? 0) > 0 && $this->count_balance == 0 )
            {
                $this->Percent = 0;
                $this->jpgPercentZero = true;
            }
            else if( $this->count_balance == 0 )
            {
                $this->Percent = 100;
            }

            // gameData is already initialized from $gameStateData
            // Remove unserialization of $this->user->session
            // Remove unserialization of $this->game->advanced for gameDataStatic
            if( !isset($gameStateData['gameDataStatic']) ) {
                $this->gameDataStatic = [];
            } else {
                $this->gameDataStatic = $gameStateData['gameDataStatic'];
            }
        }
        public function is_active()
        {
            // Remove all database checks involving $this->game->view, $this->shop->is_blocked, $this->user->is_blocked, \VanguardLTE\Session::where(...)
            return true;
        }
        public function SetGameData($key, $value)
        {
            $timeLife = 86400;
            $this->gameData[$key] = [
                'timelife' => time() + $timeLife, 
                'payload' => $value
            ];
        }
        public function GetGameData($key)
        {
            if( isset($this->gameData[$key]) ) 
            {
                return $this->gameData[$key]['payload'];
            }
            else
            {
                return 0;
            }
        }
        public function FormatFloat($num)
        {
            $str0 = explode('.', $num);
            if( isset($str0[1]) ) 
            {
                if( strlen($str0[1]) > 4 ) 
                {
                    return round($num * 100) / 100;
                }
                else if( strlen($str0[1]) > 2 ) 
                {
                    return floor($num * 100) / 100;
                }
                else
                {
                    return $num;
                }
            }
            else
            {
                return $num;
            }
        }
        public function SaveGameData()
        {
            // Method body is now empty
        }
        public function CheckBonusWin()
        {
            $allRateCnt = 0;
            $allRate = 0;
            foreach( $this->Paytable as $vl ) 
            {
                foreach( $vl as $vl2 ) 
                {
                    if( $vl2 > 0 ) 
                    {
                        $allRateCnt++;
                        $allRate += $vl2;
                        break;
                    }
                }
            }
            return $allRate / $allRateCnt;
        }
        public function GetRandomPay()
        {
            $allRate = [];
            foreach( $this->Paytable as $vl ) 
            {
                foreach( $vl as $vl2 ) 
                {
                    if( $vl2 > 0 ) 
                    {
                        $allRate[] = $vl2;
                    }
                }
            }
            shuffle($allRate);
            if( $this->game->stat_in < ($this->game->stat_out + ($allRate[0] * $this->AllBet)) ) 
            {
                $allRate[0] = 0;
            }
            return $allRate[0];
        }
        public function HasGameDataStatic($key)
        {
            if( isset($this->gameDataStatic[$key]) ) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        public function SaveGameDataStatic()
        {
            // $this->game->advanced = serialize($this->gameDataStatic);
            // $this->game->save();
            // $this->game->refresh();
        }
        public function SetGameDataStatic($key, $value)
        {
            $timeLife = 86400;
            $this->gameDataStatic[$key] = [
                'timelife' => time() + $timeLife, 
                'payload' => $value
            ];
        }
        public function GetGameDataStatic($key)
        {
            if( isset($this->gameDataStatic[$key]) ) 
            {
                return $this->gameDataStatic[$key]['payload'];
            }
            else
            {
                return 0;
            }
        }
        public function HasGameData($key)
        {
            if( isset($this->gameData[$key]) ) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        public function GetHistory()
        {
            // Remove database call \VanguardLTE\GameLog::whereRaw(...)
            // Return a default value, e.g., 'NULL' or an empty array, as history is no longer managed here.
            return 'NULL';
        }
        public function UpdateJackpots($bet)
        {
            // Make the entire method body empty.
            // Clear the $this->Jackpots array
            $this->Jackpots = [];
        }
        public function GetBank($slotState = '')
        {
            // Remove $game = $this->game;
            // Change $this->Bank = $game->get_gamebank($slotState); return $this->Bank / $this->CurrentDenom; to return $this->Bank;
            return $this->Bank;
        }
        public function GetPercent()
        {
            // Should now use $this->Percent which is set from $gameStateData['shop']['percent']
            return $this->Percent;
        }
        public function GetCountBalanceUser()
        {
            // Should use $this->count_balance set from $gameStateData['user']['count_balance']
            return $this->count_balance;
        }
        public function InternalError($errcode)
        {
            $strLog = '';
            $strLog .= "\n";
            $strLog .= ('{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
            $strLog .= "\n";
            $strLog .= ' ############################################### ';
            $strLog .= "\n";
            $slg = '';
            if( file_exists(storage_path('logs/') . $this->slotId . 'Internal.log') ) 
            {
                $slg = file_get_contents(storage_path('logs/') . $this->slotId . 'Internal.log');
            }
            file_put_contents(storage_path('logs/') . $this->slotId . 'Internal.log', $slg . $strLog);
            exit( '' );
        }
        public function InternalErrorSilent($errcode)
        {
            $strLog = '';
            $strLog .= "\n";
            $strLog .= ('{"responseEvent":"error","responseType":"' . $errcode . '","serverResponse":"InternalError","request":' . json_encode($_REQUEST) . ',"requestRaw":' . file_get_contents('php://input') . '}');
            $strLog .= "\n";
            $strLog .= ' ############################################### ';
            $strLog .= "\n";
            $slg = '';
            if( file_exists(storage_path('logs/') . $this->slotId . 'Internal.log') ) 
            {
                $slg = file_get_contents(storage_path('logs/') . $this->slotId . 'Internal.log');
            }
            file_put_contents(storage_path('logs/') . $this->slotId . 'Internal.log', $slg . $strLog);
        }
        public function SetBank($slotState = '', $sum, $slotEvent = '')
        {
            if( $this->isBonusStart || $slotState == 'bonus' || $slotState == 'freespin' || $slotState == 'respin' ) 
            {
                $slotState = 'bonus';
            }
            else
            {
                $slotState = '';
            }
            if( $this->GetBank($slotState) + $sum < 0 ) 
            {
                $this->InternalError('Bank_   ' . $sum . '  CurrentBank_ ' . $this->GetBank($slotState) . ' CurrentState_ ' . $slotState . ' Trigger_ ' . ($this->GetBank($slotState) + $sum));
            }
            // Remove all lines that call $game->set_gamebank(...) and $game->save().
            // Change to: $this->Bank += $sum;
            // Remove return $game;
            $this->Bank += $sum;
        }

        public function SetBalance($sum, $slotEvent = '')
        {
            // Remove all lines that interact with $this->user model directly
            // Change to: $this->Balance += $sum;
            // Remove return $this->user;
            $this->Balance += $sum;
        }
        public function GetBalance()
        {
            // Remove $user = $this->user;
            // Change $this->Balance = $user->balance / $this->CurrentDenom; to return $this->Balance;
            return $this->Balance;
        }
        public function SaveLogReport($spinSymbols, $bet, $lines, $win, $slotState)
        {
            // Make the entire method body empty.
        }
        public function GetSpinSettings($garantType = 'bet', $bet, $lines)
        {
            $curField = 10;
            switch( $lines ) 
            {
                case 10:
                    $curField = 10;
                    break;
                case 9:
                case 8:
                    $curField = 9;
                    break;
                case 7:
                case 6:
                    $curField = 7;
                    break;
                case 5:
                case 4:
                    $curField = 5;
                    break;
                case 3:
                case 2:
                    $curField = 3;
                    break;
                case 1:
                    $curField = 1;
                    break;
                default:
                    $curField = 10;
                    break;
            }
            if( $garantType != 'bet' ) 
            {
                $pref = '_bonus';
            }
            else
            {
                $pref = '';
            }
            $this->AllBet = $bet * $lines;
            $linesPercentConfigSpin = $this->game->get_lines_percent_config('spin');
            $linesPercentConfigBonus = $this->game->get_lines_percent_config('bonus');
            $currentPercent = $this->shop->percent;
            $currentSpinWinChance = 0;
            $currentBonusWinChance = 0;
            $percentLevel = '';
            foreach( $linesPercentConfigSpin['line' . $curField . $pref] as $k => $v ) 
            {
                $l = explode('_', $k);
                $l0 = $l[0];
                $l1 = $l[1];
                if( $l0 <= $currentPercent && $currentPercent <= $l1 ) 
                {
                    $percentLevel = $k;
                    break;
                }
            }
            $currentSpinWinChance = $linesPercentConfigSpin['line' . $curField . $pref][$percentLevel];
            $currentBonusWinChance = $linesPercentConfigBonus['line' . $curField . $pref][$percentLevel];
            $RtpControlCount = 200;
            if( !$this->HasGameDataStatic('SpinWinLimit') ) 
            {
                $this->SetGameDataStatic('SpinWinLimit', 0);
            }
            if( !$this->HasGameDataStatic('RtpControlCount') ) 
            {
                $this->SetGameDataStatic('RtpControlCount', $RtpControlCount);
            }
            if( ($this->game->stat_in ?? 0) > 0 )
            {
                $rtpRange = ($this->game->stat_out ?? 0) / $this->game->stat_in * 100;
            }
            else
            {
                $rtpRange = 0;
            }
            if( $this->GetGameDataStatic('RtpControlCount') == 0 )
            {
                if( $currentPercent + rand(1, 2) < $rtpRange && $this->GetGameDataStatic('SpinWinLimit') <= 0 )
                {
                    $this->SetGameDataStatic('SpinWinLimit', rand(25, 50));
                }
                if( $pref == '' && $this->GetGameDataStatic('SpinWinLimit') > 0 )
                {
                    $currentBonusWinChance = 5000;
                    $currentSpinWinChance = 20;
                    $this->MaxWin = rand(1, 5);
                    if( $rtpRange < ($currentPercent - 1) )
                    {
                        $this->SetGameDataStatic('SpinWinLimit', 0);
                        $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
                    }
                }
            }
            else if( $this->GetGameDataStatic('RtpControlCount') < 0 )
            {
                if( $currentPercent + rand(1, 2) < $rtpRange && $this->GetGameDataStatic('SpinWinLimit') <= 0 )
                {
                    $this->SetGameDataStatic('SpinWinLimit', rand(25, 50));
                }
                $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
                if( $pref == '' && $this->GetGameDataStatic('SpinWinLimit') > 0 )
                {
                    $currentBonusWinChance = 5000;
                    $currentSpinWinChance = 20;
                    $this->MaxWin = rand(1, 5);
                    if( $rtpRange < ($currentPercent - 1) )
                    {
                        $this->SetGameDataStatic('SpinWinLimit', 0);
                    }
                }
                if( $this->GetGameDataStatic('RtpControlCount') < (-1 * $RtpControlCount) && $currentPercent - 1 <= $rtpRange && $rtpRange <= ($currentPercent + 2) )
                {
                    $this->SetGameDataStatic('RtpControlCount', $RtpControlCount);
                }
            }
            else
            {
                $this->SetGameDataStatic('RtpControlCount', $this->GetGameDataStatic('RtpControlCount') - 1);
            }
            $bonusWin = rand(1, $currentBonusWinChance);
            $spinWin = rand(1, $currentSpinWinChance);
            $return = [
                'none', 
                0
            ];
            if( $bonusWin == 1 && $this->slotBonus ) 
            {
                $this->isBonusStart = true;
                $garantType = 'bonus';
                $winLimit = $this->GetBank($garantType);
                $return = [
                    'bonus',
                    $winLimit
                ];
                if( ($this->game->stat_in ?? 0) < ($this->CheckBonusWin() * $bet + ($this->game->stat_out ?? 0)) || $winLimit < ($this->CheckBonusWin() * $bet) )
                {
                    $return = [
                        'none',
                        0
                    ];
                }
            }
            else if( $spinWin == 1 )
            {
                $winLimit = $this->GetBank($garantType);
                $return = [
                    'win', 
                    $winLimit
                ];
            }
            if( $garantType == 'bet' && $this->GetBalance() <= (2 / $this->CurrentDenom) ) 
            {
                $randomPush = rand(1, 10);
                if( $randomPush == 1 ) 
                {
                    $winLimit = $this->GetBank('');
                    $return = [
                        'win', 
                        $winLimit
                    ];
                }
            }
            return $return;
        }
        public function getNewSpin($game, $spinWin = 0, $bonusWin = 0, $lines, $garantType = 'bet')
        {
            $curField = 10;
            switch( $lines ) 
            {
                case 10:
                    $curField = 10;
                    break;
                case 9:
                case 8:
                    $curField = 9;
                    break;
                case 7:
                case 6:
                    $curField = 7;
                    break;
                case 5:
                case 4:
                    $curField = 5;
                    break;
                case 3:
                case 2:
                    $curField = 3;
                    break;
                case 1:
                    $curField = 1;
                    break;
                default:
                    $curField = 10;
                    break;
            }
            if( $garantType != 'bet' ) 
            {
                $pref = '_bonus';
            }
            else
            {
                $pref = '';
            }
            if( $spinWin && isset($game->game_win->{'winline' . $pref . $curField}) )
            {
                $win = explode(',', $game->game_win->{'winline' . $pref . $curField});
            }
            else if( $bonusWin && isset($game->game_win->{'winbonus' . $pref . $curField}) )
            {
                $win = explode(',', $game->game_win->{'winbonus' . $pref . $curField});
            }
            else
            {
                // Fallback if game_win properties are not set
                return 0; // Or handle as an error/default case
            }
            $number = rand(0, count($win) - 1);
            return $win[$number];
        }
        public function GetRandomScatterPos($rp, $rsym)
        {
            $rpResult = [];
            for( $i = 0; $i < count($rp); $i++ ) 
            {
                if( $rp[$i] == $rsym ) 
                {
                    if( $rsym == '2' ) 
                    {
                        if( isset($rp[$i + 1]) && isset($rp[$i - 1]) ) 
                        {
                            array_push($rpResult, $i + 1);
                        }
                    }
                    else
                    {
                        if( isset($rp[$i + 1]) && isset($rp[$i - 1]) ) 
                        {
                            array_push($rpResult, $i);
                        }
                        if( isset($rp[$i - 1]) && isset($rp[$i - 2]) ) 
                        {
                            array_push($rpResult, $i - 1);
                        }
                        if( isset($rp[$i + 1]) && isset($rp[$i + 2]) ) 
                        {
                            array_push($rpResult, $i + 1);
                        }
                    }
                }
            }
            shuffle($rpResult);
            if( !isset($rpResult[0]) ) 
            {
                $rpResult[0] = rand(2, count($rp) - 3);
            }
            return $rpResult[0];
        }
        public function GetCluster($reels)
        {
            for( $p = 0; $p <= 2; $p++ ) 
            {
                for( $r = 1; $r <= 5; $r++ ) 
                {
                    if( $reels['reel' . $r][$p] == '2' ) 
                    {
                        if( $p == 0 && $r == 1 ) 
                        {
                            $reels['reel' . $r][$p] = '2c';
                        }
                        else
                        {
                            if( isset($reels['reel' . ($r - 1)][$p]) && $reels['reel' . ($r - 1)][$p] == '2c' ) 
                            {
                                $reels['reel' . $r][$p] = '2c';
                            }
                            if( isset($reels['reel' . $r][$p - 1]) && $reels['reel' . $r][$p - 1] == '2c' ) 
                            {
                                $reels['reel' . $r][$p] = '2c';
                            }
                        }
                    }
                }
            }
            return $reels;
        }
        public function GetGambleSettings()
        {
            $spinWin = rand(1, $this->WinGamble);
            return $spinWin;
        }
        public function GetReelStrips($winType, $slotEvent)
        {
            // $game = $this->game; // game object is now a property
            if( $slotEvent == 'freespin' )
            {
                $reel = new GameReel();
                $fArr = $reel->reelsStripBonus;
                foreach( [
                    'reelStrip1', 
                    'reelStrip2', 
                    'reelStrip3', 
                    'reelStrip4', 
                    'reelStrip5', 
                    'reelStrip6'
                ] as $reelStrip ) 
                {
                    $curReel = array_shift($fArr);
                    if( count($curReel) ) 
                    {
                        $this->$reelStrip = $curReel;
                    }
                }
            }
            if( $winType != 'bonus' ) 
            {
                $prs = [];
                foreach( [
                    'reelStrip1', 
                    'reelStrip2', 
                    'reelStrip3', 
                    'reelStrip4', 
                    'reelStrip5', 
                    'reelStrip6'
                ] as $index => $reelStrip ) 
                {
                    if( is_array($this->$reelStrip) && count($this->$reelStrip) > 0 ) 
                    {
                        $prs[$index + 1] = mt_rand(0, count($this->$reelStrip) - 3);
                    }
                }
            }
            else
            {
                $randomBonusType = rand(1, 2);
                if( $randomBonusType == 1 ) 
                {
                    $reelsId = [
                        1, 
                        2, 
                        3, 
                        4, 
                        5
                    ];
                    for( $i = 0; $i < count($reelsId); $i++ ) 
                    {
                        if( $i == 0 || $i == 2 || $i == 4 ) 
                        {
                            $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->{'reelStrip' . $reelsId[$i]}, '0');
                        }
                        else
                        {
                            $prs[$reelsId[$i]] = rand(0, count($this->{'reelStrip' . $reelsId[$i]}) - 3);
                        }
                    }
                }
                else
                {
                    $reelsId = [
                        1, 
                        2, 
                        3, 
                        4, 
                        5
                    ];
                    $sCnt = rand(3, 5);
                    for( $i = 0; $i < count($reelsId); $i++ ) 
                    {
                        if( $i < $sCnt ) 
                        {
                            $prs[$reelsId[$i]] = $this->GetRandomScatterPos($this->{'reelStrip' . $reelsId[$i]}, '2');
                        }
                        else
                        {
                            $prs[$reelsId[$i]] = rand(0, count($this->{'reelStrip' . $reelsId[$i]}) - 3);
                        }
                    }
                }
            }
            $reel = [
                'rp' => []
            ];
            foreach( $prs as $index => $value ) 
            {
                $key = $this->{'reelStrip' . $index};
                $cnt = count($key);
                $key[-1] = $key[$cnt - 1];
                $key[$cnt] = $key[0];
                $reel['reel' . $index][0] = $key[$value - 1];
                $reel['reel' . $index][1] = $key[$value];
                $reel['reel' . $index][2] = $key[$value + 1];
                $reel['reel' . $index][3] = '';
                $reel['rp'][] = $value;
            }
            return $reel;
        }
    }

}
