<?php

declare(strict_types=1);

namespace VC4SM\Bundle\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use VC4SM\Bundle\Controller\AcceptRequest;
use VC4SM\Bundle\Controller\SendOffer;

// todo: schema
/**
 * @ApiResource(
 *     collectionOperations={
 *       "send_offer"={
 *         "method"="POST",
 *         "path"="/credential/send_offer",
 *         "controller"=SendOffer::class,
 *       },
 *       "accept_request"={
 *         "method"="POST",
 *         "path"="/credential/accept_request",
 *         "controller"=AcceptRequest::class,
 *       }
 *     },
 *     itemOperations={
 *       "get"
 *     },
 *     iri="https://schema.org/Place",
 *     normalizationContext={"groups"={"Credential:output"}, "jsonld_embed_context"=true},
 *     denormalizationContext={"groups"={"Credential:input"}, "jsonld_embed_context"=true}
 * )
 */
class Credential
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $identifier;

    /**
     * todo: schema.
     *
     * @Groups({"Credential:output", "Credential:input"})
     *
     * @var string
     */
    private $myDid;

    /**
     * todo: schema.
     *
     * @Groups({"Credential:output", "Credential:input"})
     *
     * @var string
     */
    private $theirDid;

    /**
     * todo: schema.
     *
     * @Groups({"Credential:output", "Credential:input"})
     *
     * @var string
     */
    private $status;


    /**
    * @param string $myDid
    * @param string $theirDid
    * @param string $status
    */
    public function __construct(string $myDid, string $theirDid, string $status)
    {
        $this->identifier = "new";
        $this->myDid = $myDid;
        $this->theirDid = $theirDid;
        $this->status = $status;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getMyDid(): string
    {
        return $this->myDid;
    }

    public function setMyDid(string $myDid): void
    {
        $this->myDid = $myDid;
    }

    public function getTheirDid(): string
    {
        return $this->theirDid;
    }

    public function setTheirDid(string $theirDid): void
    {
        $this->theirDid = $theirDid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
