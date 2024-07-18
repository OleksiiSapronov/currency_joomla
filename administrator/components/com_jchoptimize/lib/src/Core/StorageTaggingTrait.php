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

namespace JchOptimize\Core;

use JchOptimize\Core\PageCache\PageCache;

\defined('_JCH_EXEC') or exit('Restricted access');
trait StorageTaggingTrait
{
    protected function tagStorage($id): void
    {
        // If item not already set for tagging, set it
        $this->taggableCache->addItem($id, 'tag');
        // Always attempt to store tags, item could be set on another page
        $this->setStorageTags($id);
    }

    private function setStorageTags(string $id): void
    {
        $tags = $this->taggableCache->getTags($id);
        $pageCache = $this->getContainer()->get(PageCache::class);
        $currentUrl = $pageCache->getCurrentPage();
        // If current url not yet tagged, tag it for this item. If it was only tagged once tag it again, so we
        // know this item was requested at least twice so shouldn't be removed until expired.
        if (\is_array($tags) && (!\in_array($currentUrl, $tags) || 1 == \count($tags))) {
            $this->taggableCache->setTags($id, \array_merge($tags, [$currentUrl]));
        } elseif (empty($tags)) {
            $this->taggableCache->setTags($id, [$currentUrl]);
        }
    }
}
