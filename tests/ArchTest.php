<?php

arch('globals')
    ->expect(['dd', 'dump'])
    ->not->toBeUsed();
