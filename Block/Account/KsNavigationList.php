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

namespace Ksolves\MultivendorMarketplace\Block\Account;

/**
 * KsNavigationList content block
 */
class KsNavigationList extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        static $count=0;
        // This is used to overcome the error when someone with invalid ssl certificate
        // tried to access and server verfying ssl cerificate gets failed or not responding
        //  so we set verify ssl to false
        stream_context_set_default([
        'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $html = '';

        if ($count == 0) {
            $html .= '<button id="close-menu"><img src='.$this->getViewFileUrl('Ksolves_MultivendorMarketplace::images/close-btn.svg').' "></button>';
            $count++;
        }
        
        if ($this->getSubmenu() && !empty($this->getSubmenu())) {
            $submenu = '<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">';
            foreach ($this->getSubmenu() as $link) {
                if (isset($link['visible']) && $link['visible'] != 1) {
                    continue;
                }
                $submenu .= '<div class="ks-sidebar-list-dropdown-style-parent"><a class="dropdown-item ks-dropdown-content ks-sidebar-list-dropdown-style" href="'.$this->getUrl($link['path']).'"><span class="ks-sidebar-submenu-icon"> '.file_get_contents($this->getViewFileUrl($link['icon'])).' </span>
                        <span class="ks-sidebar-submenu">'.$link['label'].'</span>
                    </a></div>';
            }
            $submenu .= '</div>';
            $html =  '<li class="ks-nav-item dropdown">
                      <a class="ks-nav-link dropdown-toggle" ' . $this->getLinkAttributes() . ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                      ';
            $html .= '<div class="ks-sidebar-list-style"><span> '.file_get_contents($this->getViewFileUrl($this->getIcon())).' </span>';
            $html .= '<span class="ks-sidebar-title">' .$this->escapeHtml($this->getLabel()). '</span>';
            $html .= '<span class="dropdown-icon ks-dropdown-sb-icon ml-auto"></span>';
            $html .= '</div></a>'.$submenu.'</li>';
        } else {
            $html .= '<li class="ks-nav-item"><a class="ks-nav-link"' . $this->getLinkAttributes() . '><div class="ks-sidebar-list-style"> <span> '.file_get_contents($this->getViewFileUrl($this->getIcon())).' </span> <span class="ks-sidebar-title">' .$this->escapeHtml($this->getLabel()). '</span></div></a></li>';
        }
        
        return $html;
    }
}
