<?php
/**
 * Class that utilizes PHP's tokenizer to extract data off 
 * PHP code. For example, class names, requires etc.
 *
 * @author Jani Hartikainen <firstname at codeutopia net>
 * @package Packageizer
 * @subpackage Tokenizer
 * @copyright Jani Hartikainen 2008
 */
class App_Tokenizer
{
	private $_currentToken = null;
	private $_currentData = null;
	
	private $_tokenData = array();
	
	private $_ignoreTokens = array(T_WHITESPACE,T_COMMENT,'{','}','(',')',';');
	
	private $_matchTokens = array(
		T_CLASS => array(
			'end' => array(T_EXTENDS,T_IMPLEMENTS,'{')
		),
		T_EXTENDS => array(
			'end' => array(T_IMPLEMENTS,'{')
		),
		T_IMPLEMENTS => array(
			'end' => array('{')
		),
		T_REQUIRE => array(
			'end' => array(';')
		),
		T_REQUIRE_ONCE => array(
			'end' => array(';')
		),
		T_INTERFACE => array(
			'end' => array('{')
		),
		T_DOC_COMMENT => array(
			'end' => array(T_CLASS, T_REQUIRE, T_REQUIRE_ONCE, T_WHITESPACE)
		)
	);
	
	public function __construct()
	{
	}
	
	public function parse($data)
	{
		$tokens = token_get_all($data);
		
		while(($token = array_shift($tokens)) !== null)
		{
			if($this->_currentToken == null && !array_key_exists($token[0], $this->_matchTokens))
			{
				continue;
			}
			else if($this->_currentToken == null)
			{
				if($token[0] != T_DOC_COMMENT)
					$this->_currentToken = $token[0];
				else
				{
					//Doc comments are "block" level so their data is available right away
					$this->_tokenData[] = array('token' => T_DOC_COMMENT, 'data' => $token[1]);
				}
			}
			else
			{
				$curTokData = $this->_matchTokens[$this->_currentToken];
				if(!isset($curTokData['ignore']))
					$curTokData['ignore'] = array();
				
				if(in_array($token[0], $curTokData['end']))
				{
					$this->_tokenData[] = array('token' => $this->_currentToken, 'data' => $this->_currentData);
					$this->_currentData = null;
					$this->_currentToken = null;
					
					array_unshift($tokens, $token);
				}
				else if(!in_array($token[0], $curTokData['ignore']) && !in_array($token[0], $this->_ignoreTokens))
				{
					if(count($token) > 1)
						$this->_currentData .= $token[1];
					else 
						$this->_currentData .= $token[0];
				}
			}
		}
	}

	public function getTokenData()
	{
		return $this->_tokenData;
	}
}
