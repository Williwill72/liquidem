<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     *
     * @Assert\NotBlank(message="Veuillez poser votre question !")
     * @Assert\Length(
     *     min="15",
     *     max="255",
     *     minMessage="15 caractéres minimum svp!",
     *     maxMessage="255 caractéres maximum svp!"
     *      )
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Assert\NotBlank(message="Veuillez préciser votre question !")
     * @Assert\Length(
     *     min="30",
     *     max="100000",
     *     minMessage="30 caractéres minimum svp!",
     *     maxMessage="10 000 caractéres maximum svp!"
     *      )
     *
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $supports;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="question")
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Subject", inversedBy="questions")
     */
    private $subjects;

    public function __construct()
    {
        $this->setSupports(0);
        $this->setCreationDate(new \DateTime());
        $this->setStatus('debating');
        $this->messages = new ArrayCollection();
        $this->subjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $Title): self
    {
        $this->title = $Title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getSupports(): ?int
    {
        return $this->supports;
    }

    public function setSupports(int $supports): self
    {
        $this->supports = $supports;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setQuestion($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getQuestion() === $this) {
                $message->setQuestion(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Subject[]
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
        }

        return $this;
    }
}