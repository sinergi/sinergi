<?php

namespace Sinergi\Users\Session;

use DateTime;
use DateInterval;
use Sinergi\Users\User\UserEntityInterface;
use Sinergi\Users\User\UserRepositoryInterface;
use Sinergi\Users\Utils\Token;

trait SessionEntityTrait
{
    protected $id;
    protected $userId;
    /** @var UserEntityInterface */
    protected $user;
    protected $isLongSession = false;
    protected $expirationDatetime;
    /** @var UserRepositoryInterface */
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository = null)
    {
        $this->userRepository = $userRepository;
        $this->createId();
        $this->createExpirationDatetime();
    }

    public function isValid(): bool
    {
        return !$this->isExpired() && $this->getUser() instanceof UserEntityInterface && $this->getUser()->isActive();
    }

    public function setExpirationDatetime(DateTime $expirationDatetime): SessionEntityInterface
    {
        $this->expirationDatetime = $expirationDatetime;
        return $this;
    }

    public function getExpirationDatetime(): DateTime
    {
        return $this->expirationDatetime;
    }

    public function createExpirationDatetime()
    {
        $expirationTime = $this->isLongSession() ?
            SessionEntityInterface::LONG_EXPIRATION_TIME : SessionEntityInterface::EXPIRATION_TIME;
        $expiration = (new DateTime)->add(new DateInterval($expirationTime));
        return $this->setExpirationDatetime($expiration);
    }

    public function isExpired()
    {
        return $this->getExpirationDatetime() < new DateTime;
    }

    public function setId(string $id): SessionEntityInterface
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function createId(): SessionEntityInterface
    {
        return $this->setId($this->generateId());
    }

    public function generateId(): string
    {
        return Token::generate(128);
    }

    public function setIsLongSession(bool $isLongSession): SessionEntityInterface
    {
        $this->isLongSession = $isLongSession;
        return $this;
    }

    public function isLongSession(): bool
    {
        return $this->isLongSession;
    }

    public function setUserId(int $userId): SessionEntityInterface
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    /** @return UserEntityInterface */
    public function getUser()
    {
        if ($this->user && $this->user->getId() === $this->getUserId()) {
            return $this->user;
        } elseif (!$this->userRepository) {
            throw new \Exception('Cannot fetch user without user repository');
        }
        return $this->user = $this->userRepository->findById($this->getUserId());
    }

    public function setUser(UserEntityInterface $user): SessionEntityInterface
    {
        $this->setUserId($user->getId());
        $this->user = $user;
        return $this;
    }

    public function setUserRepository(UserRepositoryInterface $userRepository): SessionEntityInterface
    {
        $this->userRepository = $userRepository;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'isLongSession' => $this->isLongSession(),
            'expirationDatetime' => $this->getExpirationDatetime()->format('Y-m-d H:i:s'),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
