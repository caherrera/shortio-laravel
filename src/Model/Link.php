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
 * @property string createdAt
 * @property string updatedAt
 * @property integer DomainId
 * @property integer OwnerId
 * @property string secureShortURL
 * @property string shortURL
 */



class Link extends Model
{

    protected $fillable = Api::properties;



}