<?php

namespace App\Entity;

use App\Repository\UserRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(message: 'There is already an account with this username', fields: ['username'])]
#[UniqueEntity(message: 'There is already an account with this email', fields: ['email'])]
#[UniqueEntity(message: 'There is already an account with this phone number', fields: ['num_telephone'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface


{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    private ?string $photo_de_profile = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(name:"username" ,length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $num_telephone = null;



    #[ORM\Column(length: 255)]
    private ?string $date_de_naissance = null;

    #[ORM\Column(length: 255)]
    private ?string $Active = null;

    #[ORM\Column(name:"UserType",length: 255)]
    private ?string $UserType = null;

    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'id_user')]
    private Collection $avis;

    public function __construct()
    {
        $this->avis = new ArrayCollection();
    }

   

    #[ORM\Column(length: 6000)]
    private ?string $photo_de_profile = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Please enter your last name")]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Please enter your first name")]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Please enter your email")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    private ?string $email = null;

    #[ORM\Column(length: 6000)]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Please enter your username")]
    private ?string $username = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Please enter your phone number")]
    #[Assert\Length(
        exactMessage: "Your phone number should have exactly 8 digits",
        min: 8,
        max: 8
    )]
    private ?int $num_telephone = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Please choose your user type")]
    private ?string $Usertype = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "Please choose your birthdate")]
    private $date_de_naissance ;

    #[ORM\Column]
    private ?bool $Active = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhotoDeProfile(): ?string
    {
        return $this->photo_de_profile;
    }

    public function setPhotoDeProfile(string $photo_de_profile): static
    {
        $this->photo_de_profile = $photo_de_profile;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }


    public function getNumTelephone(): ?string

    public function getNumTelephone(): ?int

    {
        return $this->num_telephone;
    }


    public function setNumTelephone(string $num_telephone): static

    public function setNumTelephone(int $num_telephone): static

    {
        $this->num_telephone = $num_telephone;

        return $this;
    }


  

    public function getDateDeNaissance(): ?string
    {
        return $this->date_de_naissance;
    }

    public function setDateDeNaissance(string $date_de_naissance): static
    {
        $this->date_de_naissance = $date_de_naissance;

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->Active;
    }

    public function setActive(string $Active): static
    {
        $this->Active = $Active;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->UserType;
    }

    public function setUserType(string $UserType): static
    {
        $this->UserType = $UserType;

    public function getUserType(): ?string
    {
        return $this->Usertype;
    }

    public function setUserType(string $type): static
    {
        $this->Usertype = $type;


        return $this;
    }


    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvis(Avis $idCour): static
    {
        if (!$this->avis->contains($idCour)) {
            $this->avis->add($idCour);
            $idCour->setIdUser($this);
        }

        return $this;
    }

    public function removeAvis(Avis $idCour): static
    {
        if ($this->avis->removeElement($idCour)) {
            // set the owning side to null (unless already changed)
            if ($idCour->getIdUser() === $this) {
                $idCour->setIdUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }


}

    public function getDateDeNaissance(): ?\DateTimeInterface
{
    return $this->date_de_naissance;
}

public function setDateDeNaissance(\DateTimeInterface $date_de_naissance): self
{
    $this->date_de_naissance = $date_de_naissance;

    return $this;
}

   
    public function getRoles(): array
{
    $roles = [];

    switch ($this->Usertype) {
        case 'ARTISTE':
            $roles[] = 'ROLE_ARTISTE';
            break;
        case 'ADMIN':
            $roles[] = 'ROLE_ADMIN';
            break;
        case 'CLIENT':
            $roles[] = 'ROLE_CLIENT';
            break;
    }

    return array_unique($roles);
}

    public function getSalt()
    {
       return null;
    }

   
    /**
 * @Assert\NotBlank(message="Please enter your password")
 * @Assert\Length(min=8, max=4096)
 */
private $plainPassword;


public function getPlainPassword(): ?string
{
    return $this->plainPassword;
}

public function setPlainPassword(string $plainPassword): self
{
    $this->plainPassword = $plainPassword;

    return $this;
}
public function eraseCredentials()
{
    $this->plainPassword = null;
}
public function getUserIdentifier(): string
{
    return $this->username;
}

public function isVerified(): bool
{
    return $this->Active;
}

public function setVerified(bool $isVerified): static
{
    $this->Active = $isVerified;

    return $this;
}
public function getActive(): ?bool
{
    return $this->Active;
}

public function setActive(bool $active): self
{
    $this->Active = $active;

    return $this;
}
public function getPhoto_de_profile(): ?string
{
    return $this->photo_de_profile;
}
}

