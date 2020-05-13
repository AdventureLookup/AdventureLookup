<?php


namespace Tests\AppBundle\Service;

use AppBundle\Entity\Adventure;
use AppBundle\Entity\AdventureDocument;
use AppBundle\Entity\ChangeRequest;
use AppBundle\Entity\Review;
use AppBundle\Service\Serializer;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    public function setUp()
    {
        $this->serializer = new Serializer();
    }

    public function testSerializeChangeRequest()
    {
        $updatedAt = new \DateTime();
        $createdAt = new \DateTime();

        $changeRequest = new ChangeRequest();
        $this->setPrivateProperty($changeRequest, "id", 5);
        $changeRequest->setFieldName("field");
        $changeRequest->setComment("a comment");
        $changeRequest->setCuratorRemarks("some remarks");
        $changeRequest->setResolved(true);
        $this->setPrivateProperty($changeRequest, "updatedAt", $updatedAt);
        $this->setPrivateProperty($changeRequest, "createdAt", $createdAt);
        $this->setPrivateProperty($changeRequest, "updatedBy", "updater");
        $this->setPrivateProperty($changeRequest, "createdBy", "creator");

        $this->assertEquals([
            "id" => 5,
            "field_name" => "field",
            "comment" => "a comment",
            "curator_remarks" => "some remarks",
            "resolved" => true,
            "updated_at" => $updatedAt->format("c"),
            "created_at" => $createdAt->format("c"),
            "updated_by" => "updater",
            "created_by" => "creator"
        ], $this->serializer->serializeChangeRequest($changeRequest));
    }

    public function testSerializeReview()
    {
        $createdAt = new \DateTime();

        $review = new Review(new Adventure());
        $this->setPrivateProperty($review, "id", 5);
        $review->setRating(true);
        $review->setComment("a comment");
        $this->setPrivateProperty($review, "createdAt", $createdAt);
        $this->setPrivateProperty($review, "createdBy", "creator");

        $this->assertEquals([
            "id" => 5,
            "is_positive" => true,
            "comment" => "a comment",
            "created_at" => $createdAt->format("c"),
            "created_by" => "creator"
        ], $this->serializer->serializeReview($review));
    }

    public function testSerializeAdventureDocument()
    {
        $adventure = new AdventureDocument(5, ["Matt"], "D&D", ["Arctic"], ["Wand of Fireball"], "Wizards",
            "Modern", ["Goblin"], ["Dragon"], "An Adventure", "A Description", "an-adventure", null, null,
            "low", 55, "Cool Magazine", "Library", "https://example.com", "https://example.com/img.png",
            true, false, true, false, 10.0);

        $this->assertEquals([
            "id" => 5,
            "authors" => ["Matt"],
            "edition" => "D&D",
            "environments" => ["Arctic"],
            "items" => ["Wand of Fireball"],
            "publisher" => "Wizards",
            "setting" => "Modern",
            "common_monsters" => ["Goblin"],
            "boss_monsters" => ["Dragon"],
            "title" => "An Adventure",
            "description" => "A Description",
            "slug" => "an-adventure",
            "min_starting_level" => null,
            "max_starting_level" => null,
            "starting_level_range" => "low",
            "num_pages" => 55,
            "found_in" => "Cool Magazine",
            "part_of" => "Library",
            "official_url" => "https://example.com",
            "thumbnail_url" => "https://example.com/img.png",
            "soloable" => true,
            "has_pregenerated_characters" => false,
            "has_tactical_maps" => true,
            "has_handouts" => false,
        ], $this->serializer->serializeAdventureDocument($adventure));
    }

    private function setPrivateProperty($object, $name, $value)
    {
        $ref = new \ReflectionObject($object);
        $prop = $ref->getProperty($name);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
