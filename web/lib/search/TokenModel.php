<?php
/**
 * Created by IntelliJ IDEA.
 * User: haradakazumi
 * Date: 2017/02/12
 * Time: 21:32
 */

require_once ('./lib/Igo.php');

class TokenModel {

    private $originText;
    private $token;
    private $igo;

    public function __construct($text) {
        $this->originText = $text;
        $this->igo = new Igo("./lib/Igo/ipadic", "UTF-8");
        $this->token = $this->igo->parse($text);
		error_log(print_r($this->token,true));
    }

    /**
     * @return mixed
     */
    public function getOriginText(){
        return $this->originText;
    }

    /**
     * @return array 解析結果の形態素リスト
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * @return Igo
     */
    public function getIgo() {
        return $this->igo;
    }

	/**
	 * 動詞を返す
	 */
    public function getVerbs() {

		$result = array();

		foreach ($this->getToken() as $token) {
			error_log(print_r($token->feature,true));
			if (mb_strpos($token->feature,"動詞", 0, "UTF-8") !== false) {
				$result[] = $token;
			}
		}
		return $result;
	}

	/**
	 * @return array
	 */
	public function getNouns() {
		$result = array();

		foreach ($this->getToken() as $token) {
			if (mb_strpos($token->feature,"名詞", 0, "UTF-8") !== false) {
				$result[] = $token;
			}
		}
		return $result;
	}

	public function getInVerbsText() {
		$nouns = $this->getInVerbs();
		$result = "";

		foreach ($nouns as $noun) {
			$result .= $noun->surface;
		}

		return $result;
	}

	private function getInVerbs() {
		$result = array();

		foreach ($this->getToken() as $token) {
		    if (preg_match("/助動詞|感動詞/",$token->feature)) {
                $result[] = $token;
		        continue;
            }

			if (mb_strpos($token->feature,"動詞", 0, "UTF-8") === false) {
				$result[] = $token;
			} else {
		        break;
            }
		}
		return $result;
	}
}