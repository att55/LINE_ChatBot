<?php
/**
 * Created by IntelliJ IDEA.
 * User: haradakazumi
 * Date: 2017/02/12
 * Time: 22:27
 */
require_once ('./lib/LineMessageUtil.php');
require_once ('./lib/search/DicConstant.php');

class MessageModel {

    private $searchModel;
    private $messageObject;

    /**
     * MessageModel constructor.
     * @param SearchModel $searchModel
     */
    public function __construct($searchModel) {
        $this->searchModel = $searchModel;
        $operation = $this->searchModel->getOperation();

        if ($operation == "none") {
            $this->setNoneMessage();
        } else if ($operation == "search") {
            $this->setSingleMaterialMessage();
        } else if ($operation == "join") {
            $this->setJoinedMessage();
        } else if ($operation == "reserve"){
			$this->setReservedMessage();
		} else {
            $this->setNoneMessage();
        }
    }

    public function getMessage() {
        return $this->messageObject;
    }

    private function setSingleMaterialMessage() {
        $this->messageObject = LineMessageUtil::getTextMessage("検索する予定です");
    }

    private function setMultiMaterialMessage() {
        $this->messageObject = LineMessageUtil::getTextMessage("検索する予定です");
    }

    private function setNoneMessage() {
        $noneMessages = DicConstant::getNoneMessages();
        shuffle($noneMessages);

        error_log($noneMessages[0]);

        $this->messageObject = LineMessageUtil::getTextMessage($noneMessages[0]);
    }

    private function setReservedMessage() {
		if ($this->searchModel->getReservedMessageKey() == "1" ||
			$this->searchModel->getReservedMessageKey() == "2"
		) {
			$message = <<<EOT
ヘルプだよ！！
EOT;
			$this->messageObject = LineMessageUtil::getTextMessage($message);

		}

	}

    private function setJoinedMessage() {
        $message = <<<EOT
追加ありがとう！！
錬金術とキルヘン・ベルのことなら教えられるよ！
わからないことがあったら、なんでも聞いてね！
EOT;
         $this->messageObject = LineMessageUtil::getTextMessage($message);
    }


}