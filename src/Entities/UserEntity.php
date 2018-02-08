<?php

namespace AdvancedLearning\Oauth2Server\Entities;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use SilverStripe\Security\Member;

class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    public $member;

    /**
     * @param Member $member
     * @return ScopeEntity
     */
    public function __construct(?Member $member){
        if($member instanceof Member) {
           $this->setMember($member);
        }
    }

    /**
     * Get the Member associated with this ClientEntity.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    public function setMember(Member $member){
        $this->member = $member;
        $this->setIdentifier($member->ID);
    }
}
