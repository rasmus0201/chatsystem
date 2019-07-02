<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Contracts\Config\Repository as Config;

class CreateChatsupportTables extends Migration
{
    /**
     * The database schema.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $config = app(Config::class);

        $this->schema = Schema::connection(
            $config->get('chatsupport.storage.database.connection')
        );
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('chatsupport_chat_rooms', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->boolean('closed');
            $table->string('closed_reason');
        });

        $this->schema->create('chatsupport_chat_room_opening_hours', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('room_id');
            $table->integer('weekday')->comment('0-6 = sunday-saturday.');
            $table->time('from');
            $table->time('to');
            $table->datetime('expires_at')->nullable();

            $table->timestamps();
        });

        $this->schema->create('chatsupport_chat_user_statuses', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('priority');
            $table->string('name');
            $table->string('slug');
        });

        $this->schema->create('chatsupport_chat_users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('room_id')->nullable();
            $table->integer('status_id');
            $table->integer('user_id')->nullable();
            $table->string('session_id'); /* user can be user and agent the same session ->unique();*/
            $table->boolean('agent')->default(false);
            $table->string('name');
            $table->string('language')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip')->nullable();

            $table->timestamps();
        });

        $this->schema->create('chatsupport_chat_conversations', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('room_id');
            $table->integer('user_id')->comment('Owner of conversation');

            $table->datetime('closed_at')->nullable();
            $table->timestamps();
        });

        $this->schema->create('chatsupport_chat_conversation_participants', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('conversation_id');
            $table->integer('user_id');

            $table->datetime('connected_at')->useCurrent();
            $table->datetime('disconnected_at')->nullable();
        });

        $this->schema->create('chatsupport_chat_messages', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('conversation_id');
            $table->integer('user_id');
            $table->boolean('system')->default(false);
            $table->text('message');

            $table->timestamps();
        });

        $this->schema->create('chatsupport_chat_message_receivers', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('message_id');
            $table->integer('user_id');
        });

        $this->schema->create('chatsupport_chat_message_attachments', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('message_id');
            $table->binary('file');
            $table->string('name');
            $table->string('mime_type');
            $table->string('file_size');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('chatsupport_chat_rooms');
        $this->schema->dropIfExists('chatsupport_chat_room_opening_hours');
        $this->schema->dropIfExists('chatsupport_chat_user_statuses');
        $this->schema->dropIfExists('chatsupport_chat_users');
        $this->schema->dropIfExists('chatsupport_chat_conversations');
        $this->schema->dropIfExists('chatsupport_chat_conversation_participants');
        $this->schema->dropIfExists('chatsupport_chat_messages');
        $this->schema->dropIfExists('chatsupport_chat_message_receivers');
        $this->schema->dropIfExists('chatsupport_chat_message_attachments');
    }
}
