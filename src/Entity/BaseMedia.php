<?php

namespace App\Entity;

use App\Entity\Locale\BaseLocale;
use App\Entity\Locale\EpisodeLocale;
use App\Entity\VO\Images;
use App\Entity\VO\Rating;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\MappedSuperclass()
 */
abstract class BaseMedia
{
    use MySqlString;

    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    protected $id;
    public function getId(): UuidInterface { return $this->id; }

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->images = new Images();
        $this->rating = new Rating();
    }

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $syncAt;
    public function synced(int $delta):bool
    {
        return $this->syncAt &&
            $this->syncAt->getTimestamp() + $delta > (new \DateTime())->getTimestamp();
    }
    public function sync() { $this->syncAt = new \DateTime(); return $this;}
    public function getSynAt() { return $this->syncAt;}

    /**
     * @var string[]
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $existTranslations;
    public function getExistTranslations(): array { return $this->existTranslations ?? []; }
    public function setExistTranslations(array $existTranslations) { $this->existTranslations = $existTranslations; return $this;}
    public function addExistTranslation(string $translations) {
        if (!is_array($this->existTranslations)) {
            $this->existTranslations = [];
        }
        if (!in_array($translations, $this->existTranslations, true)) {
            $this->existTranslations[] = $translations;
        }
        $this->existTranslations = array_filter($this->existTranslations);
        sort($this->existTranslations);
        return $this;
    }

    abstract public function getTorrents();
    abstract public function getLocales();
    public function getLocale(string $locale): ?BaseLocale {
        foreach ($this->getLocales() as $localeObj) {
            if ($localeObj->getLocale() === $locale) {
                return $localeObj;
            }
        }

        return null;
    }

    //<editor-fold desc="Api Data">
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;
    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $this->clearUtf($title); return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $year;
    public function getYear() { return $this->year; }
    public function setYear($year) { $this->year = $year; return $this;}

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $synopsis;
    public function getSynopsis() { return $this->synopsis; }
    public function setSynopsis($synopsis) { $this->synopsis = $this->clearUtf($synopsis); return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $runtime;
    public function getRuntime() { return $this->runtime; }
    public function setRuntime($runtime) { $this->runtime = $runtime; return $this;}

    /**
     * @var array
     * @ORM\Column(type="simple_array")
     */
    protected $genres;
    public function getGenres() { return $this->genres; }
    public function setGenres($genres) { $this->genres = $genres; sort($this->genres); return $this;}

    /**
     * @var Images
     * @ORM\Embedded(class="App\Entity\VO\Images", columnPrefix="images_")
     */
    protected $images;
    public function getImages() { return $this->images; }

    /**
     * @var Rating
     * @ORM\Embedded(class="App\Entity\VO\Rating", columnPrefix="rating_")
     */
    protected $rating;
    public function getRating() { return $this->rating; }
    //</editor-fold>
}
