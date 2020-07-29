<?php

namespace Shortio\Laravel\Api;

class Link extends Api
{

    const properties = [
        "path",
        "title",
        "icon",
        "archived",
        "originalURL",
        "iphoneURL",
        "androidURL",
        "splitURL",
        "expiresAt",
        "expiredURL",
        "redirectType",
        "cloaking",
        "source",
        "AutodeletedAt",
        "createdAt",
        "updatedAt",
        "DomainId",
        "OwnerId",
        "secureShortURL",
        "shortURL",
        "domain"
    ];
}
