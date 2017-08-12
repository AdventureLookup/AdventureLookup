<?php


namespace Tests;

use Behat\Mink\Session;
use Tests\Fixtures\RelatedEntitiesData;
use Tests\Fixtures\UserData;

class NewAdventureTest extends BrowserTestCase
{
    const TITLE = 'My greatest Adventure';
    const SLUG = 'my-greatest-adventure';
    const DESCRIPTION = "A description.";
    const AUTHORS = ['Jonathan Schneider', 'Matt Colville'];
    const ENVIRONMENTS = ['Environment 4', 'Environment 5'];
    const ITEMS = ['Bag of Code', 'Stone of Programming'];
    const COMMON_MONSTERS = ['Ternary Operator', 'Code Style'];
    const BOSS_MONSTERS = ['Ruby', 'Rails'];
    const EDITION = 'Edition 1';
    const PUBLISHER = 'Publisher 2';
    const SETTING = 'Setting 3';
    const MIN_STARTING_LEVEL = '4444';
    const MAX_STARTING_LEVEL = '6666';
    const STARTING_LEVEL_RANGE = 'medium';
    const NUM_PAGES = '7777';
    const FOUND_IN = 'Dugeon Magazine';
    const PART_OF = 'Tales from another World';
    const LINK = 'http://example.com';
    const THUMBNAIL_URL = 'http://lorempixel.com/130/160/';

    const CREATE_ADVENTURE_PATH = '/adventure';

    /**
     * @dataProvider triggerValidationErrorProvider
     */
    public function testAddSimpleAdventure(bool $triggerValidationError)
    {
        $this->loadFixtures([UserData::class]);
        $session = $this->makeSession(true);

        $this->visit($session, self::CREATE_ADVENTURE_PATH);

        $page = $session->getPage();
        if (!$triggerValidationError) {
            $this->fillField($session, 'title', self::TITLE);
        }
        $page->findButton('Save')->click();

        if ($triggerValidationError) {
            $this->assertTrue($page->hasContent('This value should not be blank.'));
            $this->fillField($session, 'title', self::TITLE);
            $page->findButton('Save')->click();
        }

        $page = $session->getPage();

        $this->assertPath($session, '/adventures/' . self::SLUG . '');
        $this->assertTrue($page->hasContent(self::TITLE));

        $this->assertWorkingIndex($session);
    }

    /**
     * @dataProvider triggerValidationErrorProvider
     */
    public function testAddComplexAdventure(bool $triggerValidationError)
    {
        $this->loadFixtures([UserData::class, RelatedEntitiesData::class]);
        $session = $this->makeSession(true);

        $this->visit($session, self::CREATE_ADVENTURE_PATH);

        if (!$triggerValidationError) {
            $this->fillField($session, 'title', self::TITLE);
        }
        $this->fillField($session, 'description', self::DESCRIPTION);

        foreach (self::AUTHORS as $author) {
            $this->fillSelectizedInput($session, 'authors', $author, true);
        }
        $this->fillSelectizedInput($session, 'edition', self::EDITION, false);
        foreach (self::ENVIRONMENTS as $environment) {
            $this->fillSelectizedInput($session, 'environments', $environment, false);
        }
        foreach (self::ITEMS as $item) {
            $this->fillSelectizedInput($session, 'items', $item, true);
        }
        $this->fillSelectizedInput($session, 'publisher', self::PUBLISHER, false);
        $this->fillSelectizedInput($session, 'setting', self::SETTING, false);
        foreach (self::COMMON_MONSTERS as $monster) {
            $this->fillSelectizedInput($session, 'commonMonsters', $monster, true);
        }
        foreach (self::BOSS_MONSTERS as $monster) {
            $this->fillSelectizedInput($session, 'bossMonsters', $monster, true);
        }

        $this->fillField($session, 'minStartingLevel', self::MIN_STARTING_LEVEL);
        $this->fillField($session, 'maxStartingLevel', self::MAX_STARTING_LEVEL);
        $this->fillField($session, 'startingLevelRange', self::STARTING_LEVEL_RANGE);
        $this->fillField($session, 'numPages', self::NUM_PAGES);
        $this->fillField($session, 'foundIn', self::FOUND_IN);
        $this->fillField($session, 'partOf', self::PART_OF);
        $this->fillField($session, 'link', self::LINK);
        $this->fillField($session, 'thumbnailUrl', self::THUMBNAIL_URL);

        // TODO: BOOLEANS

        $page = $session->getPage();
        $page->findButton('Save')->click();

        if ($triggerValidationError) {
            $this->assertTrue($page->hasContent('This value should not be blank.'));
            $this->fillField($session, 'title', self::TITLE);
            $page->findButton('Save')->click();
        }

        $this->assertPath($session, '/adventures/' . self::SLUG . '');
        $this->assertTrue($page->hasContent(self::TITLE));
        $this->assertTrue($page->hasContent(self::DESCRIPTION));

        foreach (self::AUTHORS as $author) {
            $this->assertTrue($page->hasContent($author));
        }
        $this->assertTrue($page->hasContent(self::EDITION));
        foreach (self::ENVIRONMENTS as $environment) {
            $this->assertTrue($page->hasContent($environment));
        }
        foreach (self::ITEMS as $item) {
            $this->assertTrue($page->hasContent($item));
        }
        $this->assertTrue($page->hasContent(self::PUBLISHER));
        $this->assertTrue($page->hasContent(self::SETTING));
        foreach (self::COMMON_MONSTERS as $monster) {
            $this->assertTrue($page->hasContent($monster));
        }
        foreach (self::BOSS_MONSTERS as $monster) {
            $this->assertTrue($page->hasContent($monster));
        }

        $this->assertTrue($page->hasContent(self::MIN_STARTING_LEVEL));
        $this->assertTrue($page->hasContent(self::MAX_STARTING_LEVEL));
        $this->assertTrue($page->hasContent(self::STARTING_LEVEL_RANGE));
        $this->assertTrue($page->hasContent(self::NUM_PAGES));
        $this->assertTrue($page->hasContent(self::FOUND_IN));
        $this->assertTrue($page->hasContent(self::PART_OF));
        $this->assertTrue($page->hasContent(self::LINK));
        $this->assertTrue($page->has('css', 'img[src="' . self::THUMBNAIL_URL . '"]'));

        $this->assertWorkingIndex($session);
    }

    private function fillField(Session $session, string $name, string $value)
    {
        $session->getPage()->fillField("appbundle_adventure_{$name}", $value);
    }

    private function fillSelectizedInput(Session $session, string $name, string $value, bool $isNewValue)
    {
        $session->executeScript("
            (function () {
                var selectize = $('#appbundle_adventure_{$name}')[0].selectize;
                var options = selectize.options;
                $.each(options, function (key, option) {
                    if (option.title === '{$value}') {
                        selectize.addItem(option.value);
                        return;
                    }
                });
                selectize.createItem('{$value}');
            })()
        ");
        if ($isNewValue) {
            $page = $session->getPage();
            $page->pressButton('Add');
        }
    }

    public function triggerValidationErrorProvider()
    {
        return [
            [false],
            [true]
        ];
    }
}
