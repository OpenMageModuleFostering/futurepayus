<?phpclass FuturePay_FuturePay_Block_Process extends Mage_Payment_Block_Info{	public function getToken(){		return Mage::registry('futurepay_token');	}		public function getCalcelUrl(){		return $this->getUrl('futurepay/index/cancel');	}		public function getErrorUrl(){		return $this->getUrl('futurepay/index/error');	}		public function getSuccessUrl(){		return $this->getUrl('futurepay/index/success');	}		public function getCartIntegrationUrl(){		return Mage::helper('futurepay')->getCartIntegrationUrl();	}}