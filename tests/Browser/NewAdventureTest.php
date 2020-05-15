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
    const YEAR = '2020';
    const LINK = 'http://example.com';
    const THUMBNAIL_URL = 'http://localhost:8003/mstile-150x150.png';

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
        } else {
            $this->disableFormValidation($session);
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
     * @dataProvider complexAdventureDataProvider
     */
    public function testAddComplexAdventure(bool $triggerValidationError, bool $useStartingLevelRange)
    {
        $this->loadFixtures([UserData::class, RelatedEntitiesData::class]);
        $session = $this->makeSession(true);

        $this->visit($session, self::CREATE_ADVENTURE_PATH);

        if (!$triggerValidationError) {
            $this->fillField($session, 'title', self::TITLE);
        } else {
            $this->disableFormValidation($session);
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

        if ($useStartingLevelRange) {
            $this->fillSelectizedInput($session, 'startingLevelRange', self::STARTING_LEVEL_RANGE, false);
        } else {
            $this->fillField($session, 'minStartingLevel', self::MIN_STARTING_LEVEL);
            $this->fillField($session, 'maxStartingLevel', self::MAX_STARTING_LEVEL);
        }
        $this->fillField($session, 'numPages', self::NUM_PAGES);
        $this->fillSelectizedInput($session, 'foundIn', self::FOUND_IN, false);
        $this->fillSelectizedInput($session, 'partOf', self::PART_OF, false);
        $this->fillField($session, 'year', self::YEAR);
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

        $this->assertPath($session, '/adventures/' . self::SLUG);
        $adventureContainer = $page->findById('adventure-container');
        $this->assertContains(self::TITLE, $adventureContainer->getText());
        $this->assertContains(self::DESCRIPTION, $adventureContainer->getText());

        foreach (self::AUTHORS as $author) {
            $this->assertContains($author, $adventureContainer->getText());
        }
        $this->assertContains(self::EDITION, $adventureContainer->getText(), '', true);
        foreach (self::ENVIRONMENTS as $environment) {
            $this->assertContains($environment, $adventureContainer->getText());
        }
        foreach (self::ITEMS as $item) {
            $this->assertContains($item, $adventureContainer->getText());
        }
        $this->assertContains(self::PUBLISHER, $adventureContainer->getText());
        $this->assertContains(self::SETTING, $adventureContainer->getText());
        foreach (self::COMMON_MONSTERS as $monster) {
            $this->assertContains($monster, $adventureContainer->getText());
        }
        foreach (self::BOSS_MONSTERS as $monster) {
            $this->assertContains($monster, $adventureContainer->getText());
        }

        if ($useStartingLevelRange) {
            $this->assertContains(self::STARTING_LEVEL_RANGE, $adventureContainer->getText(), '', true);
        } else {
            $this->assertContains(self::MIN_STARTING_LEVEL, $adventureContainer->getText());
            $this->assertContains(self::MAX_STARTING_LEVEL, $adventureContainer->getText());
        }
        $this->assertContains(self::NUM_PAGES, $adventureContainer->getText());
        $this->assertContains(self::FOUND_IN, $adventureContainer->getText());
        $this->assertContains(self::YEAR, $adventureContainer->getText());
        $this->assertContains(self::PART_OF, $adventureContainer->getText());
        $this->assertContains(self::LINK, $adventureContainer->getText());
        $this->assertTrue($adventureContainer->has('css', 'img[src="' . self::THUMBNAIL_URL . '"]'));

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
                var found = false;
                $.each(options, function (key, option) {
                    if (option.title === '{$value}') {
                        selectize.addItem(option.value);
                        found = true;
                    }
                });
                if (!found) {
                    selectize.createItem('{$value}');
                }
            })()
        ");
        if ($isNewValue) {
            $session->getPage()->pressButton('Add');
        }
    }

    public function triggerValidationErrorProvider()
    {
        return [
            [false],
            [true]
        ];
    }

    public function complexAdventureDataProvider()
    {
        return [
            // trigger error, use starting level range
            [false, false],
            [false, true],
            [true, false],
        ];
    }
}
