<?php

namespace Bundsgaard\ChatSupport\Storage;

class Conversation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chatsupport_chat_conversations';

    protected $guarded = ['id'];

    protected $hidden = [
        'user_id',
        'room_id',
        'closed_at',
        'created_at',
        'updated_at',
    ];

    private $newMessage = [
        'excludes' => [],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function participants()
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function activeParticipants()
    {
        return $this->hasMany(ConversationParticipant::class)->whereNull('disconnected_at');
    }

    public function message($message, $system = false)
    {
        $message = $this->messages()->create([
            'user_id' => $system ? null : $this->user_id,
            'system' => !!$system,
            'message' => $message,
        ]);

        foreach ($this->activeParticipants as $participant) {
            if (in_array($participant->user_id, $this->newMessage['excludes'])) {
                continue;
            }

            $message->receivers()->create([
                'user_id' => $participant->user_id
            ]);
        }
    }

    public function excludes($users)
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        for ($i = 0; $i < count($users); $i++) {
            if (is_integer($users[$i])) {
                continue;
            }

            // Set the user from a provided model/object.
            $users[$i] = $users[$i]->user_id;
        }

        $this->newMessage['excludes'] = array_unique(array_merge($this->newMessage['excludes'], $users));

        return $this;
    }
}
