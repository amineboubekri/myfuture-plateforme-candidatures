<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Models\User;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_create_a_message()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $application = Application::factory()->create();
        
        $messageData = [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'application_id' => $application->id,
            'subject' => 'Application Update',
            'content' => 'Your application has been reviewed.',
            'read_at' => null,
        ];

        $message = Message::create($messageData);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($sender->id, $message->sender_id);
        $this->assertEquals($receiver->id, $message->receiver_id);
        $this->assertEquals($application->id, $message->application_id);
        $this->assertEquals('Application Update', $message->subject);
        $this->assertEquals('Your application has been reviewed.', $message->content);
        $this->assertNull($message->read_at);
    }

    /** @test */
    public function it_has_sender_relationship()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        $this->assertInstanceOf(User::class, $message->sender);
        $this->assertEquals($sender->id, $message->sender->id);
    }

    /** @test */
    public function it_has_receiver_relationship()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        $this->assertInstanceOf(User::class, $message->receiver);
        $this->assertEquals($receiver->id, $message->receiver->id);
    }

    /** @test */
    public function it_has_application_relationship()
    {
        $application = Application::factory()->create();
        $message = Message::factory()->create(['application_id' => $application->id]);

        $this->assertInstanceOf(Application::class, $message->application);
        $this->assertEquals($application->id, $message->application->id);
    }

    /** @test */
    public function it_can_mark_as_read()
    {
        $message = Message::factory()->create(['read_at' => null]);
        
        $message->update(['read_at' => now()]);
        
        $this->assertNotNull($message->fresh()->read_at);
    }

    /** @test */
    public function it_can_mark_as_unread()
    {
        $message = Message::factory()->create(['read_at' => now()]);
        
        $message->update(['read_at' => null]);
        
        $this->assertNull($message->fresh()->read_at);
    }

    /** @test */
    public function it_has_fillable_fields()
    {
        $message = new Message();
        $fillable = $message->getFillable();

        $this->assertContains('sender_id', $fillable);
        $this->assertContains('receiver_id', $fillable);
        $this->assertContains('application_id', $fillable);
        $this->assertContains('subject', $fillable);
        $this->assertContains('content', $fillable);
        $this->assertContains('read_at', $fillable);
    }

    /** @test */
    public function it_can_get_messages_by_sender()
    {
        $sender1 = User::factory()->create();
        $sender2 = User::factory()->create();
        $receiver = User::factory()->create();
        
        Message::factory()->count(3)->create([
            'sender_id' => $sender1->id,
            'receiver_id' => $receiver->id
        ]);
        
        Message::factory()->count(2)->create([
            'sender_id' => $sender2->id,
            'receiver_id' => $receiver->id
        ]);

        $sender1Messages = Message::where('sender_id', $sender1->id)->get();
        $sender2Messages = Message::where('sender_id', $sender2->id)->get();

        $this->assertEquals(3, $sender1Messages->count());
        $this->assertEquals(2, $sender2Messages->count());
    }

    /** @test */
    public function it_can_get_messages_by_receiver()
    {
        $sender = User::factory()->create();
        $receiver1 = User::factory()->create();
        $receiver2 = User::factory()->create();
        
        Message::factory()->count(4)->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver1->id
        ]);
        
        Message::factory()->count(3)->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver2->id
        ]);

        $receiver1Messages = Message::where('receiver_id', $receiver1->id)->get();
        $receiver2Messages = Message::where('receiver_id', $receiver2->id)->get();

        $this->assertEquals(4, $receiver1Messages->count());
        $this->assertEquals(3, $receiver2Messages->count());
    }

    /** @test */
    public function it_can_get_messages_by_application()
    {
        $application1 = Application::factory()->create();
        $application2 = Application::factory()->create();
        
        Message::factory()->count(5)->create(['application_id' => $application1->id]);
        Message::factory()->count(3)->create(['application_id' => $application2->id]);

        $app1Messages = Message::where('application_id', $application1->id)->get();
        $app2Messages = Message::where('application_id', $application2->id)->get();

        $this->assertEquals(5, $app1Messages->count());
        $this->assertEquals(3, $app2Messages->count());
    }

    /** @test */
    public function it_can_get_read_messages()
    {
        $readMessage = Message::factory()->create(['read_at' => now()]);
        $unreadMessage = Message::factory()->create(['read_at' => null]);

        $readMessages = Message::whereNotNull('read_at')->get();
        $unreadMessages = Message::whereNull('read_at')->get();

        $this->assertEquals(1, $readMessages->count());
        $this->assertEquals(1, $unreadMessages->count());
    }

    /** @test */
    public function it_can_get_unread_messages()
    {
        $readMessage = Message::factory()->create(['read_at' => now()]);
        $unreadMessage1 = Message::factory()->create(['read_at' => null]);
        $unreadMessage2 = Message::factory()->create(['read_at' => null]);

        $unreadMessages = Message::whereNull('read_at')->get();

        $this->assertEquals(2, $unreadMessages->count());
    }

    /** @test */
    public function it_can_get_recent_messages()
    {
        $oldMessage = Message::factory()->create(['created_at' => now()->subDays(10)]);
        $newMessage = Message::factory()->create(['created_at' => now()]);

        $recentMessages = Message::orderBy('created_at', 'desc')->get();

        $this->assertEquals($newMessage->id, $recentMessages->first()->id);
        $this->assertEquals($oldMessage->id, $recentMessages->last()->id);
    }

    /** @test */
    public function it_can_get_messages_with_sender_data()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        $messageWithSender = Message::with('sender')->find($message->id);

        $this->assertInstanceOf(User::class, $messageWithSender->sender);
        $this->assertEquals($sender->id, $messageWithSender->sender->id);
    }

    /** @test */
    public function it_can_get_messages_with_receiver_data()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $message = Message::factory()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);

        $messageWithReceiver = Message::with('receiver')->find($message->id);

        $this->assertInstanceOf(User::class, $messageWithReceiver->receiver);
        $this->assertEquals($receiver->id, $messageWithReceiver->receiver->id);
    }

    /** @test */
    public function it_can_get_messages_with_application_data()
    {
        $application = Application::factory()->create();
        $message = Message::factory()->create(['application_id' => $application->id]);

        $messageWithApplication = Message::with('application')->find($message->id);

        $this->assertInstanceOf(Application::class, $messageWithApplication->application);
        $this->assertEquals($application->id, $messageWithApplication->application->id);
    }

    /** @test */
    public function it_can_get_conversation_between_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Message::factory()->count(3)->create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id
        ]);
        
        Message::factory()->count(2)->create([
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id
        ]);

        $conversation = Message::where(function($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                  ->where('receiver_id', $user2->id);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                  ->where('receiver_id', $user1->id);
        })->get();

        $this->assertEquals(5, $conversation->count());
    }

    /** @test */
    public function it_can_get_total_messages_count()
    {
        Message::factory()->count(8)->create();
        
        $totalCount = Message::count();
        
        $this->assertEquals(8, $totalCount);
    }

    /** @test */
    public function it_can_get_messages_by_read_status_count()
    {
        Message::factory()->count(6)->create(['read_at' => now()]);
        Message::factory()->count(4)->create(['read_at' => null]);

        $readCount = Message::whereNotNull('read_at')->count();
        $unreadCount = Message::whereNull('read_at')->count();

        $this->assertEquals(6, $readCount);
        $this->assertEquals(4, $unreadCount);
    }

    /** @test */
    public function it_can_get_messages_by_application_count()
    {
        $application1 = Application::factory()->create();
        $application2 = Application::factory()->create();
        
        Message::factory()->count(7)->create(['application_id' => $application1->id]);
        Message::factory()->count(5)->create(['application_id' => $application2->id]);

        $app1Count = Message::where('application_id', $application1->id)->count();
        $app2Count = Message::where('application_id', $application2->id)->count();

        $this->assertEquals(7, $app1Count);
        $this->assertEquals(5, $app2Count);
    }

    /** @test */
    public function it_can_check_if_message_is_read()
    {
        $readMessage = Message::factory()->create(['read_at' => now()]);
        $unreadMessage = Message::factory()->create(['read_at' => null]);

        $this->assertTrue($readMessage->read_at !== null);
        $this->assertTrue($unreadMessage->read_at === null);
    }

    /** @test */
    public function it_can_get_messages_by_subject()
    {
        Message::factory()->create(['subject' => 'Application Update']);
        Message::factory()->create(['subject' => 'Document Request']);
        Message::factory()->create(['subject' => 'Application Update']);

        $updateMessages = Message::where('subject', 'Application Update')->get();
        $requestMessages = Message::where('subject', 'Document Request')->get();

        $this->assertEquals(2, $updateMessages->count());
        $this->assertEquals(1, $requestMessages->count());
    }
}

