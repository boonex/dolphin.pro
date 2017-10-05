<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolForm.php' );

class BxBaseFormView extends BxDolForm
{
    var $bEnableErrorIcon = true;

    /**
     * HTML Code of this form
     *
     * @var string
     */
    var $sCode;

    /**
     * Code which will be added to the beginning of the form.
     * For example, hidden inputs.
     * For internal use only
     *
     * @var string
     */
    var $_sCodeAdd = '';

    var $_isTbodyOpened = false;

    var $_isDateControl = false;
    var $_isDateTimeControl = false;

    /**
     * Constructor
     *
     * @param array $aInfo Form contents
     *
     * $aInfo['params'] = array(
     *     'remove_form' => true|false,
     * );
     *
     * @return BxBaseFormView
     */
    function __construct($aInfo)
    {
        parent::__construct($aInfo);
    }

    /**
     * Return Form code
     *
     * @return string
     */
    function getCode()
    {
        return ($this->sCode = $this->genForm());
    }

    /**
     * Generate the whole form
     *
     * @return string
     */
    function genForm()
    {
        $sTable = $this->genTable();
		if(!empty($this->aParams['remove_form']))
			return $sTable;

		// add default className to attributes
		$this->aFormAttrs['class'] = 'form_advanced' . (isset($this->aFormAttrs['class']) ? (' ' . $this->aFormAttrs['class']) : '');
		$sFormAttrs = $this->convertArray2Attrs($this->aFormAttrs);

		return '<form ' . $sFormAttrs . '>' . $sTable . '</form>';
    }

    /**
     * Generate Table HTML code
     *
     * @return string
     */
    function genTable()
    {
        // add default className to attributes
        $this->aTableAttrs['class'] = 'form_advanced_table' . (isset($this->aTableAttrs['class']) ? (' ' . $this->aTableAttrs['class']) : '');

        // add CSRF token if it's needed.
        if($GLOBALS['MySQL']->getParam('sys_security_form_token_enable') == 'on' && !defined('BX_DOL_CRON_EXECUTE') && (!isset($this->aParams['csrf']['disable']) || (isset($this->aParams['csrf']['disable']) && $this->aParams['csrf']['disable'] !== true)) && ($mixedCsrfToken = BxDolForm::getCsrfToken()) !== false)
            $this->aInputs['csrf_token'] = array(
                'type' => 'hidden',
                'name' => 'csrf_token',
                'value' => $mixedCsrfToken,
                'db' => array (
                    'pass' => 'Xss',
                )
            );

        // generate table contents
        $sTableContent = '';
        foreach ($this->aInputs as $aInput)
            $sTableContent .= $this->genRow($aInput);

        $this->addCssJs($this->_isDateControl, $this->_isDateTimeControl);
        return $this->_sCodeAdd . $GLOBALS['oSysTemplate']->parseHtmlByName('form_content.html', array(
        	'wrapper_id' => $this->id,
        	'table_attrs' => $this->convertArray2Attrs($this->aTableAttrs),
        	'content' => $this->getOpenTbody() . $sTableContent . $this->getCloseTbody(),
        ));
    }

    /**
     * Generate single Table Row
     *
     * @param  array  $aInput
     * @return string
     */
    function genRow(&$aInput)
    {
        switch ($aInput['type']) {
            case 'headers':
                $sRow = $this->genRowHeaders($aInput);
            break;

            case 'block_header':
                $sRow = $this->genRowBlockHeader($aInput);
            break;

            case 'block_end':
                $sRow = $this->genBlockEnd($aInput);
            break;

            case 'hidden':
                // do not generate row for hidden inputs
                $sRow = '';
                $this->_sCodeAdd .= $this->genInput($aInput);
            break;

            case 'select_box':
                $sRow = $this->genRowSelectBox($aInput);
            break;

            default:
                $sRow = $this->genRowStandard($aInput);
        }

        return $sRow;
    }

    /**
     * Generate standard row
     *
     * @param  array  $aInput
     * @return string
     */
    function genRowStandard(&$aInput)
    {
        $sCaption = !empty($aInput['caption']) ? $aInput['caption'] : '';
        $sRequired  = !empty($aInput['required']) ? '<span class="bx-form-required">*</span>' : '';
        $sClassAdd = '';

        $sInfoIcon = !empty($aInput['info']) ? $this->genInfoIcon($aInput['info']) : '';
        $sErrorIcon = $this->genErrorIcon(empty($aInput['error']) ? '' : $aInput['error']);

        $bMultiplyable = isset($aInput['attrs']) && isset($aInput['value']) && is_array($aInput['value']) && $aInput['attrs']['multiplyable'];
        if($bMultiplyable) {
            $sValFirst = array_shift($aInput['value']);
            $aInputCopy = $aInput;
            $aInputCopy['value'] = $sValFirst;
            $sInputCopy = $this->genInput($aInputCopy);
            $sInputCode = $this->genWrapperInput($aInputCopy, $sInputCopy);
            $sInputCodeExtra = '';
            foreach ($aInput['value'] AS $v) {
                unset($aInputCopy['attrs']['multiplyable']);
                $aInputCopy['attrs']['deletable'] = 'true';
                $aInputCopy['value'] = $v;
                $sInputCopy = $this->genInput($aInputCopy);
                $sInputCodeExtra .= '<div class="clear_both"></div>' . $this->genWrapperInput($aInputCopy, $sInputCopy);
            }
        } else {
            $sInput     = $this->genInput($aInput);
            $sInputCode = $this->genWrapperInput($aInput, $sInput);
        }

        $sElementClass = '';
        if(!empty($aInput['tr_attrs']['class'])) {
        	$sElementClass = ' ' . $aInput['tr_attrs']['class'];
        	unset($aInput['tr_attrs']['class']);
        }

        if($bMultiplyable)
        	$sElementClass .= ' bx-form-element-multiplyable';

        if(!empty($aInput['error']))
        	$sElementClass .= ' bx-form-element-error';

        $sCode = '';
        $sCode .= $this->getOpenTbody();
		$sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_row_standard_' . (!empty($aInput['colspan']) ? 'colspan' : 'simple') . '.html', array(
			'element_type' => 'bx-form-element-' . $aInput['type'],
			'element_class' => $sElementClass,
			'element_attrs' => $this->convertArray2Attrs(!empty($aInput['tr_attrs']) ? $aInput['tr_attrs'] : ''),
			'class_add' => $sClassAdd,
			'required' => $sRequired, 
			'caption' => $sCaption,
			'input_code' => !empty($sInputCode) ? $sInputCode : '',
			'info_icon' => !empty($sInfoIcon) ? $sInfoIcon : '',
			'error_icon' => !empty($sErrorIcon) ? $sErrorIcon : '',
			'input_code_extra' => !empty($sInputCodeExtra) ? $sInputCodeExtra : '',
			'bx_if:show_toggle_html' => array(
				'condition' => isset($aInput['html']) && (isset($aInput['html_toggle']) && $aInput['html_toggle']),
				'content' => array(
					'attrs_id' => (!isset($aInput['attrs']['id'])) ?: $aInput['attrs']['id'],
				),
			),
		));

        return $sCode;
    }

    /**
     * Generate select_box row
     *
     * @param  array  $aInput
     * @return string
     */
    function genRowSelectBox(&$aInput)
    {
        $sCaption = (!empty($aInput['caption'])) ? $aInput['caption'] : '&nbsp;';
        $sRequired = (!empty($aInput['required'])) ? '<span class="bx-form-required">*</span>' : '';
        $sClassAdd = '';

        $sInfoIcon  = (!empty($aInput['info'])) ? $this->genInfoIcon($aInput['info']) : '';
        $sErrorIcon = $this->genErrorIcon(empty($aInput['error']) ? '' : $aInput['error']);

        $sInput = $this->genInputSelectBox($aInput);

	    $sElementClass = '';
        if(!empty($aInput['tr_attrs']['class'])) {
        	$sElementClass = ' ' . $aInput['tr_attrs']['class'];
        	unset($aInput['tr_attrs']['class']);
        }

        if(!empty($aInput['error']))
        	$sElementClass .= ' bx-form-element-error';

        $sCode = $this->getOpenTbody();
        $sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_row_select_' . (!empty($aInput['colspan']) ? 'colspan' : 'simple') . '.html', array(
        	'element_type' => 'bx-form-element-' . $aInput['type'],
        	'element_class' => $sElementClass,
			'element_attrs' => $this->convertArray2Attrs(!empty($aInput['tr_attrs']) ? $aInput['tr_attrs'] : ''),
			'class_add' => $sClassAdd,
			'required' => $sRequired, 
			'caption' => $sCaption,
			'input_code' => $sInput,
        	'info_icon' => !empty($sInfoIcon) ? $sInfoIcon : '',
			'error_icon' => !empty($sErrorIcon) ? $sErrorIcon : '',
		));

        return $sCode;
    }
    /**
     * Generate Table Headers row
     *
     * @param  array  $aInput
     * @return string
     */
    function genRowHeaders(&$aInput)
    {
        $sCode = $this->getCloseTbody();
        $sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_row_headers.html', array(
        	'class' => isset($aInput['tr_class']) ? ' ' . $aInput['tr_class'] : '',
        	'header_first' => $aInput[0],
        	'header_second' => $aInput[1]
        ));
        $sCode .= $this->getOpenTbody();

        return $sCode;
    }

    /**
     * Generate Block Headers row
     *
     * @param  array  $aInput
     * @return string
     */
    function genRowBlockHeader(&$aInput)
    {
        $sElementClass = '';
		$aNextFieldsetAdd = false;
		$bCollapsable = $bCollapsed = false;
        if(isset($aInput['collapsable']) && $aInput['collapsable']) {
        	$bCollapsable = true;
            $sElementClass = ' collapsable';

            if(isset($aInput['collapsed']) && $aInput['collapsed']) {
            	$bCollapsed = true;
                $sElementClass .= ' collapsed';
                $aNextFieldsetAdd = array(
                    'style' => 'display:none;',
                );
            }
        }

        $aAttrs = !empty($aInput['attrs']) ? $aInput['attrs'] : '';

        $sClass = '';
        if(isset($aAttrs['class'])) {
			$sClass = ' ' . $aAttrs['class'];
			unset($aAttrs['class']);
        }

        $sCode = $this->getCloseTbody();
        $sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_row_block_header.html', array(
        	'element_class' => $sElementClass,
        	'class' => $sClass,
        	'attrs' => $this->convertArray2Attrs($aAttrs),
        	'caption' => $aInput['caption'],
        	'bx_if:show_collapse' => array(
        		'condition' => $bCollapsable,
        		'content' => array(
        			'icon_name' => $bCollapsed ? 'chevron-down' : 'chevron-up'
        		)
        	)
        ));
        $sCode .= $this->getOpenTbody($aNextFieldsetAdd);

        return $sCode;
    }

    function genBlockEnd()
    {
        return $this->getCloseTbody() . $this->getOpenTbody();
    }

    function genWrapperInput($aInput, $sContent)
    {
        return $GLOBALS['oSysTemplate']->parseHtmlByName('form_input_wrapper.html', array(
        	'type' => $aInput['type'],
            'class' => isset($aInput['wrap_text']) && $aInput['wrap_text'] ? 'input_wrapper_wraptext' : '',
        	'attrs' => isset($aInput['attrs_wrapper']) && is_array($aInput['attrs_wrapper']) ? $this->convertArray2Attrs($aInput['attrs_wrapper']) : '',
        	'content' => $sContent,
        	'content_add' => ''
        ));
    }

    /**
     * Generate HTML Input Element
     *
     * @param  array  $aInput
     * @return string Output HTML Code
     */
    function genInput(&$aInput)
    {
        $sDivider = isset($aInput['dv']) ? $aInput['dv'] : ' ';

        switch ($aInput['type']) {

            // standard inputs (and non-standard, interpreted as standard)
            case 'datetime':
                $this->_isDateTimeControl = true;
            case 'date':
                $this->_isDateControl = true;
            case 'text':
            case 'number':
            case 'email':
            case 'url':
            case 'checkbox':
            case 'radio':
            case 'image':
            case 'password':
            case 'slider':
            case 'range':
            case 'doublerange':
            case 'hidden':
                $sInput = $this->genInputStandard($aInput);
            break;

            case 'file':
                if (!isset($aInput['attrs']['size']))
                    $aInput['attrs']['size'] = 12;
                $sInput = $this->genInputStandard($aInput);
            break;

            case 'button':
            case 'reset':
            case 'submit':
                $sInput = $this->genInputButton($aInput);
            break;

            case 'textarea':
                $sInput = $this->genInputTextarea($aInput);
            break;

            case 'select':
                $sInput = $this->genInputSelect($aInput);
            break;

            case 'select_multiple':
                $sInput = $this->genInputSelectMultiple($aInput);
            break;

            case 'checkbox_set':
                $sInput = $this->genInputCheckboxSet($aInput);
            break;

            case 'radio_set':
                $sInput = $this->genInputRadioSet($aInput);
            break;

            case 'input_set': // numeric array of inputs
                $sInput = '';

                foreach ($aInput as $iKey => $aSubInput) {
                    if (!is_int($iKey) or !$aSubInput)
                        continue; // parse only integer keys and existing values

                    $sInput .= $this->genInput($aSubInput); // recursive call
                    $sInput .= $sDivider;
                }
            break;

            case 'custom':
                $sInput = isset($aInput['content']) ? $aInput['content'] : '';
            break;

            case 'canvas':
                //TODO: do we need canvas?
            break;

            case 'captcha':
                $sInput = $this->genInputCaptcha($aInput);
            break;

            case 'value':
                $sInput = $aInput['value'];
            break;

            default:
                //unknown control type
                $sInput = 'Unknown control type';
        }

        // create input label
        $sInput .= $this->genLabel($aInput);

        return $sInput;
    }

    /**
     * Generate new Input Element id
     *
     * @param  array  $aInput
     * @return string
     */
    function getInputId(&$aInput)
    {
        if (isset($aInput['id']))
            return $aInput['id'];

        $sName = preg_replace("/[^a-z0-9]/i", '_', $aInput['name']); 

        $sID = $this->id . '_input_' . $sName;

        if ( // multiple elements cause identical id's
            (
                (
                    $aInput['type'] == 'checkbox' and
                    substr($aInput['name'], -2) == '[]' // it is multiple element
                ) or
                $aInput['type'] == 'radio' // it is always multiple (i think so... hm)
            ) and
            isset($aInput['value']) // if we can make difference
        ) {
            $sValue = md5($aInput['value']);

            // add value
            $sID .= '_' . $sValue;
        }

        $sID = trim($sID, '_');

        $aInput['id'] = $sID; // just for repeated calls

        return $sID;
    }

    /**
     * Generate standard Input Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputStandard(&$aInput)
    {
    	$sInputName = 'input';

        // clone attributes for system use ;)
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']} bx-def-font-inputs" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        if(isset($aInput['type'])) {
        	switch($aInput['type']) {
        		case 'datetime':
        			$aAttrs['type'] = 'date_time';
        			break;
        		case 'date':
        			$aAttrs['type'] = 'date_calendar';
        			break;
        		case 'file':
        			$sInputName = 'file';
        			$aAttrs['type'] = $aInput['type'];
        			break;
        		default:
        			$aAttrs['type'] = $aInput['type'];
        	}
        }
        if (isset($aInput['name'])) $aAttrs['name']  = $aInput['name'];
        if (isset($aInput['value'])) $aAttrs['value'] = $aInput['value'];

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        // for checkboxes
        if (isset($aInput['checked']) and $aInput['checked'])
            $aAttrs['checked'] = 'checked';

        $sInputAttrs = $this->convertArray2Attrs($aAttrs);
        return $this->getInput($sInputName, $sInputAttrs);
    }

    /**
     * Generate standard Button/Reset/Submit Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputButton(&$aInput)
    {
    	$aInput['colspan'] = 2;

        // clone attributes for system use ;)
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']} bx-btn" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');
        $aAttrs['type']  = $aInput['type'];
        $aAttrs['name']  = $aInput['name'];
        $aAttrs['value'] = $aInput['value'];

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        // for checkboxes
        if(isset($aInput['checked']) && $aInput['checked'])
            $aAttrs['checked'] = 'checked';

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        return $this->getInput('button', $sAttrs);
    }

    /**
     * Generate Textarea Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputTextarea(&$aInput)
    {
        // for inputs with labels generate id
        if (!isset($aInput['attrs']['id']) && (isset($aInput['label']) || isset($aInput['html'])))
            $aInput['attrs']['id'] = $this->getInputId($aInput);

        // clone attributes for system use ;)
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] =
            "form_input_{$aInput['type']} bx-def-font-inputs" .
            (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '') .
            ((isset($aInput['html']) and $aInput['html'] and $this->addHtmlEditor($aInput['html'], $aInput)) ? ' form_input_html' : '');

        $aAttrs['name']  = $aInput['name'];

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        $sValue = (isset($aInput['value'])) ? htmlspecialchars_adv($aInput['value']) : '';

        return $this->getInput('textarea', $sAttrs, $sValue);
    }

    function addHtmlEditor($iViewMode, &$aInput)
    {
        bx_import('BxDolEditor');
        $oEditor = BxDolEditor::getObjectInstance();
        if (!$oEditor)
            return false;

        if (isset($aInput['html_no_link_conversion']) && $aInput['html_no_link_conversion'])
            $oEditor->setCustomConf ("remove_script_host: false,\nrelative_urls: false,\n");

        $this->_sCodeAdd .= $oEditor->attachEditor ((!empty($this->aFormAttrs['id']) ? '#' . $this->aFormAttrs['id'] . ' ': '') . '[name="'.$aInput['name'].'"]', $iViewMode, isset($aInput['dynamic']) ? $aInput['dynamic'] : false);
        if (!isset($aInput['attrs_wrapper']['style']))
            $aInput['attrs_wrapper']['style'] = '';
        $aInput['attrs_wrapper']['style'] = 'width:' . $oEditor->getWidth($iViewMode) . $aInput['attrs_wrapper']['style'];

        return true;
    }

    /**
     * Generate Select Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputSelect(&$aInput)
    {
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']} bx-def-font-inputs" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        $aAttrs['name'] = $aInput['name'];

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        // generate options
        $sCurValue = isset($aInput['value']) ? $aInput['value'] : null;
        $sOptions = '';

        if (isset($aInput['values']) and is_array($aInput['values'])) {
            foreach ($aInput['values'] as $sValue => $sTitle) {
                if(is_array($sTitle)) {
                    $sValue = $sTitle['key'];
                    $sTitle = $sTitle['value'];
                }
                $sValueC = htmlspecialchars_adv($sValue);
                $sTitleC = htmlspecialchars_adv($sTitle);

                $aAttrsOption = array('value' => $sValueC);
                if((string)$sValue === (string)$sCurValue)
                	$aAttrsOption['selected'] = 'selected';

                $sOptions .= $this->getInput('option', $this->convertArray2Attrs($aAttrsOption), $sTitleC);
            }
        }

        // generate element
        return $this->getInput('select', $sAttrs, $sOptions);
    }

    /**
     * Generate Select Box Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputSelectBox(&$aInput, $sInfo = '', $sError = '')
    {
        $sCode = '';

        if (isset($aInput['value']) and is_array($aInput['value'])) {
            $iCounter = 0;
            foreach ($aInput['value'] as $sValue) {
                $aNewInput = $aInput;

                $aNewInput['name'] .= '[]';
                $aNewInput['value'] = $sValue;

                if (isset($aInput['values'][$sValue])) { // draw select if value exists in values

                    $aNewInput['type'] = 'select';

                    if ($iCounter == 0) { // for the first input create multiplyable select and add info and error icons (if set)
                        $aNewInput['attrs']['multiplyable'] = 'true';
                        $aNewInput['attrs']['add_other']    = isset($aNewInput['attrs']['add_other']) ? $aNewInput['attrs']['add_text'] : 'true';
                        $sInputAdd = $sInfo . ' ' . $sError;
                    } else { // for the others inputs create only deletable
                        $aNewInput['attrs']['deletable'] = 'true';
                        $sInputAdd = '';
                    }

                    $iCounter ++;
                } else { // draw text input for non-existent value (man, it is select_box, wow!)
                    $aNewInput['type'] = 'text';
                    $aNewInput['attrs']['deletable'] = 'true';
                }

                $sInput = $this->genInput($aNewInput);

				$sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_input_wrapper.html', array(
		        	'type' => $aInput['type'],
		        	'class' => '',
		        	'attrs' => '',
		        	'content' => $sInput,
		        	'content_add' => $sInputAdd
		        ));
            }
        } 
        else {
            // clone
            $aNewInput = $aInput;

            $aNewInput['type'] = 'select';
            $aNewInput['name'] .= '[]';
            $aNewInput['attrs']['multiplyable'] = 'true';
            $aNewInput['attrs']['add_other']    =
                isset($aNewInput['attrs']['add_other'])
                    ? (empty($aNewInput['attrs']['add_text']) ? '' :  $aNewInput['attrs']['add_text'])
                    : 'true';

            $sInput = $this->genInput($aNewInput);

			$sCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('form_input_wrapper.html', array(
		        	'type' => $aInput['type'],
		        	'class' => '',
		        	'attrs' => '',
		        	'content' => $sInput,
		        	'content_add' => $sInfo . $sError
		        ));
        }

        return $sCode;
    }

    /**
     * Generate Multiple Select Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputSelectMultiple(&$aInput)
    {
        $aAttrs = $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']} bx-def-font-inputs" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        $aAttrs['name']     = $aInput['name'] . '[]';
        $aAttrs['multiple'] = 'multiple';

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        // generate options
        $aCurValues = $aInput['value'] ? (is_array($aInput['value']) ? $aInput['value'] : array($aInput['value'])) : array();
        $sOptions = '';

        if (isset($aInput['values']) and is_array($aInput['values'])) {
            foreach ($aInput['values'] as $sValue => $sTitle) {
                $sValueC = htmlspecialchars_adv($sValue);
                $sTitleC = htmlspecialchars_adv($sTitle);

                $aAttrsOption = array('value' => $sValueC);
                if(in_array($sValue, $aCurValues))
                	$aAttrsOption['selected'] = 'selected';

				$sOptions .= $this->getInput('option', $this->convertArray2Attrs($aAttrsOption), $sTitleC);

            }
        }

        // generate element
        return $this->getInput('select', $sAttrs, $sOptions);
    }

    /**
     * Generate Checkbox Set Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputCheckboxSet(&$aInput)
    {
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']}" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        $aAttrs['name']  = $aInput['name'];

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        // generate options
        $sDivider = isset($aInput['dv']) ? $aInput['dv'] : ' ';
        $aCurValues = $aInput['value'] ? (is_array($aInput['value']) ? $aInput['value'] : array($aInput['value'])) : array();

        $sOptions = '';

        if (isset($aInput['values']) and is_array($aInput['values'])) {
            if (count($aInput['values']) > 3 && $sDivider == ' ')
                $sDivider = '<br />';
            // generate complex input using simple standard inputs
            foreach ($aInput['values'] as $sValue => $sLabel) {
                // create new simple input
                $aNewInput = array(
                    'type' => 'checkbox',
                    'name' => $aInput['name'] . '[]',
                    'value' => $sValue,
                    'checked' => in_array($sValue, $aCurValues),
                    'label' => $sLabel,
                );

                $sNewInput  = $this->genInput($aNewInput);

                // attach new input to complex
                $sOptions .= ($sNewInput . $sDivider);
            }
        }

        // generate element
        return $this->getInput('checkbox_set', $sAttrs, $sOptions);
    }
    /**
     * Generate Radiobuttons Set Element
     *
     * @param  array  $aInput
     * @return string
     */
    function genInputRadioSet(&$aInput)
    {
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']}" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        $aAttrs['name']  = $aInput['name'];

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        // generate options
        $sDivider = isset($aInput['dv']) ? $aInput['dv'] : ' ';
        $sCurValue = isset($aInput['value']) ? $aInput['value'] : '';

        $sOptions = '';

        if (isset($aInput['values']) and is_array($aInput['values'])) {
            if (count($aInput['values']) > 3 && $sDivider == ' ')
                $sDivider = '<br />';
            // generate complex input using simple standard inputs
            foreach ($aInput['values'] as $sValue => $sLabel) {
                // create new simple input
                $aNewInput = array(
                    'type'    => 'radio',
                    'name'    => $aInput['name'],
                    'value'   => $sValue,
                    'checked' => ((string)$sValue === (string)$sCurValue),
                    'label'   => $sLabel,
                );

                $sNewInput  = $this->genInput($aNewInput);

                // attach new input to complex
                $sOptions .= ($sNewInput . $sDivider);
            }
        }

        // generate element
        return $this->getInput('radio_set', $sAttrs, $sOptions);
    }

    function genInputCaptcha(&$aInput)
    {
        $aAttrs = empty($aInput['attrs']) ? array() : $aInput['attrs'];

        // add default className to attributes
        $aAttrs['class'] = "form_input_{$aInput['type']}" . (isset($aAttrs['class']) ? (' ' . $aAttrs['class']) : '');

        // for inputs with labels generate id
        if (isset($aInput['label']))
            $aAttrs['id'] = $this->getInputId($aInput);

        $sAttrs = $this->convertArray2Attrs($aAttrs);

        bx_import('BxDolCaptcha');
        $oCaptcha = BxDolCaptcha::getObjectInstance();
        $sCaptcha = $oCaptcha ? $oCaptcha->display(isset($aInput['dynamic']) ? $aInput['dynamic'] : false) : _t('_sys_txt_captcha_not_available');
        $sCaptcha .= $this->getInput('input', $this->convertArray2Attrs(array('type' => 'hidden', 'name' => $aInput['name'])));

        return $this->getInput('captcha', $sAttrs, $sCaptcha);
    }

    /**
     * Generate Label Element
     *
     * @param  string $sLabel   Text of the Label
     * @param  string $sInputID Dependant Input Element ID
     * @return string HTML code
     */
    function genLabel(&$aInput)
    {
        if (!isset($aInput['label']) or empty($aInput['label']))
            return '';

        $sLabel   = $aInput['label'];
        $sInputID = $this->getInputId($aInput);

        return $this->getInput('label', $this->convertArray2Attrs(array('for' => $sInputID)), $sLabel);
    }

    /**
     * Convert array to attributes string
     *
     * <code>
     * $a = array('name' => 'test', 'value' => 5);
     * $s = $this->convertArray2Attrs($a);
     * echo $s;
     * </code>
     *
     * Output:
     * name="test" value="5"
     *
     * @param  array  $a
     * @return string
     */
    function convertArray2Attrs($a)
    {
        $sRet = '';

        if (is_array($a)) {
            foreach ($a as $sKey => $sValue) {

                if (!isset($sValue) || is_null($sValue)) // pass NULL values
                    continue;

                $sValueC = htmlspecialchars_adv($sValue);

                $sRet .= " $sKey=\"$sValueC\"";
            }
        }

        return $sRet;
    }

    function genInfoIcon($sInfo)
    {
        return $this->getInput('icon_info', $this->convertArray2Attrs(array(
        	'class' => 'sys-icon info-circle', 
        	'float_info' => htmlspecialchars_adv($sInfo)
        )));
    }

    function genErrorIcon( $sError = '' )
    {
        if (!$this->bEnableErrorIcon)
            return '';

        $sErrorH  = ' '; // it has space because jquery doesnt accept it if it is empty
        if ($sError) {
            $sError = str_replace( "\n", "\\n", $sError );
            $sError = str_replace( "\r", "",    $sError );
            $sErrorH  = htmlspecialchars_adv($sError);
        }

        return $this->getInput('icon_error', $this->convertArray2Attrs(array(
        	'class' => 'sys-icon exclamation-circle', 
        	'float_info' => $sErrorH
        )));
    }

    function getOpenTbody($aAttrsAdd = false)
    {
    	if($this->_isTbodyOpened)
    		return '';

		$this->_isTbodyOpened = true;

		$aAttrsAdd['class'] = 'bx-form-fields-wrapper' . (!empty($aAttrsAdd['class']) ? ' ' . $aAttrsAdd['class'] : '');

		return '<fieldset ' . $this->convertArray2Attrs($aAttrsAdd) . '>';
    }

    function getCloseTbody()
    {
		if(!$this->_isTbodyOpened) 
    		return '';

		$this->_isTbodyOpened = false;

		return '</fieldset>';
    }

    function getInput($sType, $sAttrs, $sContent = '')
    {
    	$sResult = '';
    	switch($sType) {
    		case 'input':
    			$sResult = '<input ' . $sAttrs . ' />';
    			break;
    		case 'file':
    			$sResult = '<label class="bx-btn form_input_multiply_select"><input ' . $sAttrs . ' /><span>' . _t('_Select file') . '</span></label>';
    			break;
    		case 'button':
    			$sResult = '<input ' . $sAttrs . ' />';
    			break;
    		case 'textarea':
    			$sResult = '<textarea ' . $sAttrs . '>' . $sContent . '</textarea>';
    			break;
    		case 'option':
    			$sResult = '<option ' . $sAttrs . '>' . $sContent . '</option>';
    			break;
    		case 'select':
    			$sResult = '<select ' . $sAttrs . '>' . $sContent . '</select>';
    			break;
    		case 'captcha':
    		case 'radio_set':
    		case 'checkbox_set':
    			$sResult = '<div ' . $sAttrs . '>' . $sContent . '</div>';
    			break;
    		case 'label':
    			$sResult = '<label ' . $sAttrs . '>' . $sContent . '</label>';
    			break;
    		case 'icon_info':
    			$sResult = '<span class="bx-form-info"><i ' . $sAttrs . '></i></span>';
    			break;
    		case 'icon_error':
    			$sResult = '<span class="bx-form-error"><i ' . $sAttrs . '></i></span>';
    			break;
    	}
    	return $sResult;
    }

    function addCssJs($isDateControl = false, $isDateTimeControl = false)
    {
    	$aTranslations = array(
    		'_add',
    		'_add_other',
    		'_Remove'
    	);
        $aJs = array(
            'jquery.ui.core.min.js',
            'jquery.ui.widget.min.js',
            'jquery.ui.mouse.min.js',
            'jquery.ui.slider.min.js',
        );

        $aCss = array(
            'forms_adv.css',
            'plugins/jquery/themes/|jquery-ui.css',
        );

        $aLang = bx_lang_info();
        $sLanguageCountry = str_replace('_', '-', $aLang['LanguageCountry']);

        if ($isDateControl || $isDateTimeControl) {

            $aUiLangs = array ('af' => 1, 'ar-DZ' => 1, 'ar' => 1, 'az' => 1, 'be' => 1, 'bg' => 1, 'bs' => 1, 'ca' => 1, 'cs' => 1, 'cy-GB' => 1, 'da' => 1, 'de' => 1, 'el' => 1, 'en-AU' => 1, 'en-GB' => 1, 'en-NZ' => 1, 'en' => 1, 'eo' => 1, 'es' => 1, 'et' => 1, 'eu' => 1, 'fa' => 1, 'fi' => 1, 'fo' => 1, 'fr-CA' => 1, 'fr-CH' => 1, 'fr' => 1, 'gl' => 1, 'he' => 1, 'hi' => 1, 'hr' => 1, 'hu' => 1, 'hy' => 1, 'id' => 1, 'is' => 1, 'it' => 1, 'ja' => 1, 'ka' => 1, 'kk' => 1, 'km' => 1, 'ko' => 1, 'ky' => 1, 'lb' => 1, 'lt' => 1, 'lv' => 1, 'mk' => 1, 'ml' => 1, 'ms' => 1, 'nb' => 1, 'nl-BE' => 1, 'nl' => 1, 'nn' => 1, 'no' => 1, 'pl' => 1, 'pt-BR' => 1, 'pt' => 1, 'rm' => 1, 'ro' => 1, 'ru' => 1, 'sk' => 1, 'sl' => 1, 'sq' => 1, 'sr-SR' => 1, 'sr' => 1, 'sv' => 1, 'ta' => 1, 'th' => 1, 'tj' => 1, 'tr' => 1, 'uk' => 1, 'vi' => 1, 'zh-CN' => 1, 'zh-HK' => 1, 'zh-TW' => 1);

            // detect language
            if (isset($aUiLangs[$sLanguageCountry]))
                $sLang = $sLanguageCountry;
            elseif (isset($aUiLangs[$aLang['Name']]))
                $sLang = $aLang['Name'];
            else 
                $sLang = 'en';

            $aJs [] = 'jquery.ui.datepicker.min.js';
            $aJs [] = 'plugins/jquery/i18n/|jquery.ui.datepicker-' . $sLang . '.js';
        }

        if ($isDateTimeControl) {

            $aCalendarLangs = array ('af' => 1, 'am' => 1, 'bg' => 1, 'ca' => 1, 'cs' => 1, 'da' => 1, 'de' => 1, 'el' => 1, 'es' => 1, 'et' => 1, 'eu' => 1, 'fa' => 1, 'fi' => 1, 'fr' => 1, 'gl' => 1, 'he' => 1, 'hr' => 1, 'hu' => 1, 'id' => 1, 'it' => 1, 'ja' => 1, 'ko' => 1, 'lt' => 1, 'lv' => 1, 'mk' => 1, 'nl' => 1, 'no' => 1, 'pl' => 1, 'pt-BR' => 1, 'pt' => 1, 'ro' => 1, 'ru' => 1, 'sk' => 1, 'sl' => 1, 'sr-RS' => 1, 'sr-YU' => 1, 'sv' => 1, 'th' => 1, 'tr' => 1, 'uk' => 1, 'vi' => 1, 'zh-CN' => 1, 'zh-TW' => 1);

            $aJs[] = 'jquery-ui-timepicker-addon.min.js';
            $aJs[] = 'jquery-ui-sliderAccess.js';

            // detect language
            if (isset($aCalendarLangs[$sLanguageCountry]))
                $aJs[] = 'plugins/jquery/i18n/|jquery-ui-timepicker-' . $sLanguageCountry . '.js';
            elseif (isset($aCalendarLangs[$aLang['Name']]))
                $aJs[] = 'plugins/jquery/i18n/|jquery-ui-timepicker-' . $aLang['Name'] . '.js';

            $aCss[] = 'plugins/jquery/themes/|jquery-ui-timepicker-addon.css';
        }

        if (isset($GLOBALS['oSysTemplate'])) {
        	$GLOBALS['oSysTemplate']->addCss($aCss);
            $GLOBALS['oSysTemplate']->addJs($aJs);
            $GLOBALS['oSysTemplate']->addJsTranslation($aTranslations);            
        }
        if (isset($GLOBALS['oAdmTemplate'])) {
            $GLOBALS['oAdmTemplate']->addJs($aJs);
            $GLOBALS['oAdmTemplate']->addCss($aCss);
        }
    }
}
