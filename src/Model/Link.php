<?php

namespace Shortio\Laravel\Model;

use Shortio\Laravel\Api\Link as Api;


/**
 * Class Link
 *
 * @package Shortio\Laravel\Model
 *
 * @property string path
 * @property string title
 * @property string icon
 * @property string archived
 * @property string originalURL
 * @property string iphoneURL
 * @property string androidURL
 * @property string splitURL
 * @property string expiresAt
 * @property string expiredURL
 * @property string redirectType
 * @property string cloaking
 * @property string source
 * @property string AutodeletedAt
 * @property-read string createdAt
 * @property-read string updatedAt
 * @property-read integer DomainId
 * @property-read integer OwnerId
 * @property-read string secureShortURL
 * @property-read string shortURL
 */



class Link extends Model
{

    protected $fillable = Api::properties;

    public function domain() {
        return new Domain();
    }


    public function all()
    {
        $domains = $this->domain()->all();
        $links=collect();
        foreach($domains as $domain) {
            $this->getApi()->get();
        }

    }

}