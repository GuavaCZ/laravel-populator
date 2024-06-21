<?php

it('loads package config file', function(){
    expect(config('populator'))->toBe([]);
});