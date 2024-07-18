<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\FeatureHelpers;

use JchOptimize\Core\Html\Parser as HtmlParser;
use JchOptimize\Platform\Profiler;

\defined('_JCH_EXEC') or exit('Restricted access');
class ReduceDom extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    public function process(string $html): string
    {
        if (!$this->params->get('pro_reduce_dom', '0')) {
            return $html;
        }
        JCH_DEBUG ? Profiler::start('ReduceDom', \false) : null;
        $options = [
            'num-elements' => 0,
            // number of elements encountered
            'nesting-level' => 0,
            'in-comments' => \false,
            // Inside a section being commented out
            'processing' => \false,
            // Maximum number of elements reached and DOM is now being reduced
            'html-block' => '',
        ];
        $regex = '#(?:[^<]*+(?:'.HtmlParser::htmlHeadElementToken().'|'.HtmlParser::htmlCommentToken().'))?[^<]*+<(/)?(\\w++)[^>]*+>#si';
        $reducedHtml = \preg_replace_callback($regex, function ($matches) use (&$options) {
            /** @var array{num-elements:int, nesting-level:int, in-comments:bool, processing:bool, html-block:string} $options */
            // Initialize return string
            $return = '';
            $bEndComments = \false;

            /** @var list<null|string> $htmlSections */
            $htmlSections = $this->params->get('pro_html_sections', ['section', 'header', 'footer', 'aside', 'nav']);

            switch (\true) {
                // Open tag
                case !empty($matches[2]) && empty($matches[1]):
                    // Increment count of elements
                    $options['num-elements']++;
                    if ($options['processing'] && \in_array($matches[2], $htmlSections) && $options['nesting-level']++ == 0) {
                        $return .= '<div class="jch-reduced-dom-container"><template class="jch-template">';
                        $options['in-comments'] = \true;
                        $options['html-block'] = $matches[2];
                    }
                    // Start commenting out sections of HTML above 600 DOM elements
                    if (600 == $options['num-elements']) {
                        $options['processing'] = \true;
                    }

                    break;
                    // Closing tag
                case !empty($matches[1]):
                    if ($options['in-comments'] && \in_array($matches[2], $htmlSections) && 0 == --$options['nesting-level'] && $matches[2] == $options['html-block']) {
                        $bEndComments = \true;
                    }

                    break;

                default:
                    break;
            }
            $return .= $matches[0];
            if ($bEndComments) {
                $return .= '</template></div>';
                $options['in-comments'] = \false;
            }

            return $return;
        }, $html);
        JCH_DEBUG ? Profiler::stop('ReduceDom', \true) : null;

        return $reducedHtml;
    }
}
