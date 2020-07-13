<?php

declare(strict_types=1);


namespace diduhless\parties\party;


use diduhless\parties\session\Session;
use diduhless\parties\session\SessionFactory;
use diduhless\parties\utils\ConfigGetter;

class Party {

    /** @var string */
    private $id;

    /** @var Session */
    private $leader;

    /** @var Session[] */
    private $members = [];

    /** @var bool */
    private $public = false;

    /** @var int */
    private $slots;

    public function __construct(string $id, Session $leader) {
        $this->id = $id;
        $this->leader = $leader;
        $this->members[] = $leader;
        $this->slots = ConfigGetter::getMaximumSlots();
    }

    public function getId(): string {
        return $this->id;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function getLeader(): Session {
        return $this->leader;
    }

    public function getLeaderName(): string {
        return $this->leader->getPlayer()->getName();
    }

    public function getSlots(): int {
        return $this->slots;
    }

    public function hasMember(Session $session): bool {
        return in_array($session, $this->members, true);
    }

    public function hasMemberByName(string $username): bool {
        return SessionFactory::hasSessionByName($username) ? $this->hasMember(SessionFactory::getSessionByName($username)) : false;
    }

    public function isPublic(): bool {
        return $this->public;
    }
    public function isFull(): bool {
        return count($this->members) >= $this->slots;
    }

    public function setLeader(Session $leader): void {
        $this->leader = $leader;
    }

    public function setPublic(bool $public): void {
        $this->public = $public;
    }

    public function setSlots(int $slots): void {
        $this->slots = $slots;
    }

    public function add(Session $session): void {
        if(!$this->hasMember($session)) {
            $this->members[] = $session;
        }
        $session->setParty($this);
    }

    public function remove(Session $session): void {
        if($this->hasMember($session)) {
            unset($this->members[array_search($session, $this->members, true)]);
        }
        $session->setParty(null);
    }

    /*
     * If you set a session instance to $ignoredMember, that session will not receive the party message
     */
    public function message(string $message, ?Session $ignoredMember = null): void {
        foreach($this->members as $member) {
            if($ignoredMember !== null and $member->getUsername() === $ignoredMember->getUsername()) continue;
            $member->message($message);
        }
    }

}