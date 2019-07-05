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
        'exclude' => [],
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

    public function disconnect($user)
    {
        if (!is_integer($user)) {
            $user = $user->id;
        }

        $this->participants()->where('user_id', $user)->first()->disconnect();

        return $this;
    }

    public function close()
    {
        $this->closed_at = new \DateTime();
        $this->save();

        return $this;
    }

    public function message($message, $system = false)
    {
        $message = $this->messages()->create([
            'user_id' => $system ? null : $this->user_id,
            'system' => !!$system,
            'message' => $message,
        ]);

        // Everybody should receivce the message,
        // because if they connect again they would not see these messages when they were gone.
        foreach ($this->participants as $participant) {
            if (in_array($participant->user_id, $this->newMessage['exclude'])) {
                continue;
            }

            $message->receivers()->create([
                'user_id' => $participant->user_id
            ]);
        }

        return $message;
    }

    public function exclude($users)
    {
        if (!is_array($users)) {
            $users = [$users];
        }

        for ($i = 0; $i < count($users); $i++) {
            if (is_integer($users[$i])) {
                continue;
            }

            // Set the user from a provided model/object.
            $users[$i] = $users[$i]->id;
        }

        $this->newMessage['exclude'] = array_unique(array_merge($this->newMessage['exclude'], $users));

        return $this;
    }
}
