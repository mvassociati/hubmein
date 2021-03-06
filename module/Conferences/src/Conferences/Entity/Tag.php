<?php
namespace Conferences\Entity;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection;

use Conferences\Entity\Conference;
/**
 * Conferences\Entity\Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="Conferences\Entity\Repository\TagRepository")
 */
class Tag
{
    /**
     * @var integer $id
     *
     * @ORM\Column( type="integer", nullable=false )
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="tag_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column( type="string", length=100, nullable=false )
     */
    private $name;
    
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Conferences\Entity\Conference", mappedBy="tagsObjects")
     */
    private $conferences;

    
    /*
     * Start of doctrine generated getters / setters
    */
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }
    
    /**
     * Add events
     *
     * @param \Conferences\Entity\Conference $events
     * @return Tag
     */
    public function addConference( Conference $conference )
    {
        $this->conferences[] = $conference;
    
        return $this;
    }

    /**
     * Remove events
     *
     * @param \Conferences\Entity\Conference $events
     */
    public function removeConference( Conference $conference )
    {
        $this->conferences->removeElement($conference);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getConferences()
    {
        return $this->conferences;
    }
}
