<div class="top-section">
<?php
if(!empty($this->article)){
echo "<h2>".$this->article->title.'</h2>';
echo "<hr>";
echo JHtml::_('content.prepare', $this->article->introtext);
}
?>
</div>