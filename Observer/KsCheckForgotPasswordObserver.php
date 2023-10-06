<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
namespace Ksolves\MultivendorMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Captcha\Observer\CaptchaStringResolver;

/**
 * Class KsCheckForgotPasswordObserver
 */
class KsCheckForgotPasswordObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $ksHelper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $ksActionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $ksRedirect;

    /**
     * @var CaptchaStringResolver
     */
    protected $ksCaptchaStringResolver;

    /**
     * @param \Magento\Captcha\Helper\Data $ksHelper
     * @param \Magento\Framework\App\ActionFlag $ksActionFlag
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $ksRedirect
     * @param CaptchaStringResolver $ksCaptchaStringResolver
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $ksHelper,
        \Magento\Framework\App\ActionFlag $ksActionFlag,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Magento\Framework\App\Response\RedirectInterface $ksRedirect,
        CaptchaStringResolver $ksCaptchaStringResolver
    ) {
        $this->ksHelper = $ksHelper;
        $this->ksActionFlag = $ksActionFlag;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksRedirect = $ksRedirect;
        $this->ksCaptchaStringResolver = $ksCaptchaStringResolver;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $ksObserver
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $ksObserver)
    {
        $ksFormId = 'user_forgotpassword';
        $ksCaptchaModel = $this->ksHelper->getCaptcha($ksFormId);
        if ($ksCaptchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $ksController */
            $ksController = $ksObserver->getControllerAction();
            if (!$ksCaptchaModel->isCorrect($this->ksCaptchaStringResolver->resolve($ksController->getRequest(), $ksFormId))) {
                $this->ksMessageManager->addErrorMessage(__('Incorrect CAPTCHA'));
                $this->ksActionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->ksRedirect->redirect($ksController->getResponse(), 'multivendor/forgotpassword/index');
            }
        }
        return $this;
    }
}