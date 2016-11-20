<?php

class InventoryTest extends TestCase
{
    use MakesInventoryRequests;

    /** @test */
    public function it_will_fetch_the_request_type()
    {
        $this->makeInventoryRequest('add')->dump()
            ->assertResponseOk();
    }
}