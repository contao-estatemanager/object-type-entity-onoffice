<?php

namespace ContaoEstateManager\ObjectTypeEntityOnOffice;

use Contao\Backend;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpClient\HttpClient;
use ContaoEstateManager\ObjectTypeEntity\ObjectTypeModel;

class OnOfficeObjectType extends Backend
{
    /**
     * Setup onoffice object types import
     *
     * @return string
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function setupImport()
    {
        if (Input::post('FORM_SUBMIT') == 'tl_objecttype_import')
        {
            if ($lang = trim(Input::post('language')))
            {
                $this->startImport($lang);

                if(!Message::hasMessages())
                {
                    $container = System::getContainer();
                    Message::addConfirmation($GLOBALS['TL_LANG']['tl_object_type']['importComplete']);
                    $this->redirect($container->get('router')->generate('contao_backend', array('do'=>'objectTypes')));
                }
            }
            else
            {
                Message::addError($GLOBALS['TL_LANG']['tl_object_type']['errNoLanguage']);
            }
        }

        // Return the form
        return Message::generate() . '
<div id="tl_buttons">
    <a href="' . ampersand(str_replace('&key=importObjectTypes', '', Environment::get('request'))) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>
<form action="' . ampersand(Environment::get('request')) . '" id="tl_theme_import" class="tl_form tl_edit_form" method="post" enctype="multipart/form-data">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" value="tl_objecttype_import">
        <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
        <div class="tl_tbox">
            <div class="widget">
                <h3>' . $GLOBALS['TL_LANG']['tl_object_type']['language'][0] . '</h3>
                <input type="text" name="language" id="language" class="tl_text" required onfocus="Backend.getScrollOffset()">
                <p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['tl_object_type']['language'][1] . '</p>
            </div>
            <div class="widget">
              <div id="ctrl_truncate" class="tl_checkbox_single_container"><input type="checkbox" name="truncate" id="opt_truncate_0" class="tl_checkbox" value="1" onfocus="Backend.getScrollOffset()"> <label for="opt_truncate_0">' . $GLOBALS['TL_LANG']['tl_object_type']['truncate'][0] . '</label></div>
              <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['tl_object_type']['truncate'][1] . '</p>
            </div>
        </div>
        <div class="tl_formbody_submit">
            <div class="tl_submit_container">
              <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['tl_object_type']['importObjectTypes'][0] . '</button>
            </div>
        </div>
    </div>
</form>';
    }

    /**
     * Import onoffice object types
     *
     * @param $lang
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function startImport($lang)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', Environment::get('url') . '/api/onoffice/v1/fields', [
            'query' => [
                'language'  => $lang,
                'labels'    => true,
                'modules'   => ['estate']
            ]
        ]);

        $statusCode = $response->getStatusCode();

        if($statusCode == '200')
        {
            // Truncate table
            if (Input::post('truncate'))
            {
                $this->truncateObjectTypes();
            }

            // Get response as array
            $arrData = $response->toArray();

            if($arrData['status']['errorcode'] != 0)
            {
                Message::addError($arrData['status']['message']);
                return;
            }

            if(!count($arrData['data']['records']))
            {
                Message::addError($GLOBALS['TL_LANG']['tl_object_type']['emptyRecords']);
                return;
            }

            // Import object types
            $this->importObjectTypes($arrData['data']['records'][0]['elements']['objektart']);
        }
        else
        {
            Message::addError($response->getInfo()['error']);
        }
    }

    /**
     * Import onoffice object type
     *
     * @param $arrObjectTypes
     */
    public function importObjectTypes($arrObjectTypes)
    {
        if (empty($arrObjectTypes) || !is_array($arrObjectTypes['permittedvalues']))
        {
            Message::addError($GLOBALS['TL_LANG']['tl_object_type']['emptyRecords']);
            return;
        }
        else
        {
            $arrObjectTypes = $arrObjectTypes['permittedvalues'];
        }

        foreach ($arrObjectTypes as $key => $label)
        {
            $root = new ObjectTypeModel();

            $root->title        = $label;
            $root->oid          = $key;
            $root->tstamp       = time();
            $root->published    = 1;

            $root->save();
        }
    }

    /**
     * Truncate object types
     */
    private function truncateObjectTypes()
    {
        $objDatabase = Database::getInstance();

        // Truncate the table
        $objDatabase->execute("TRUNCATE TABLE tl_object_type");
    }
}
