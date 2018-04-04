<?php

/**
* @package   s9e\terribletables
* @copyright Copyright (c) 2018 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\terribletables;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return ['core.text_formatter_s9e_configure_after' => 'onConfigure'];
	}

	public function onConfigure($event)
	{
		$configurator = $event['configurator'];

		$configurator->BBCodes->addCustom(
			'[table $forceLookahead=true]{TEXT}[/table]',
			'<table><xsl:apply-templates select="TR"/></table>'
		);
		$configurator->BBCodes->addCustom(
			'[tr]{TEXT}[/tr]',
			'<tr><xsl:apply-templates select="TD"/></tr>'
		);
		$configurator->BBCodes->addCustom(
			'[td]{TEXT}[/td]',
			'<td>{TEXT}</td>'
		);

		$configurator->tags['table']->filterChain->append(__CLASS__ . '::splitTagContent')
			->resetParameters()
			->addParameterByName('parser')
			->addParameterByName('tag')
			->addParameterByName('text')
			->addParameterByValue("\n")
			->addParameterByValue('TR');

		$configurator->tags['tr']->filterChain->append(__CLASS__ . '::splitTagContent')
			->resetParameters()
			->addParameterByName('parser')
			->addParameterByName('tag')
			->addParameterByName('text')
			->addParameterByValue('|')
			->addParameterByValue('TD');
	}

	public static function splitTagContent($parser, $tag, $text, $separator, $tagName)
	{
		if ($tag->getEndTag())
		{
			$lpos = $tag->getPos() + $tag->getLen();
			$rpos = $tag->getEndTag()->getPos();
			$text = substr($text, $lpos, $rpos - $lpos);

			foreach (explode($separator, $text) as $str)
			{
				$len = strlen($str);
				$parser->addTagPair($tagName, $lpos, 0, $lpos + $len, 0);
				$lpos += $len + strlen($separator);
			}
		}

		return true;
	}
}