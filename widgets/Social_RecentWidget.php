<?php
namespace Craft;

class Social_RecentWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Recent Social Posts');
    }

    public function getBodyHtml()
    {
        return craft()->templates->render(
            'social/recent'
        );
    }
}
