<?php

namespace Laravel\Passport;

class ClientRepository
{
    /**
     * Get a client by the given ID.
     *
     * @param  int  $id
     * @return \Laravel\Passport\Client|null
     */
    public function find($id)
    {
        return Client::find($id);
    }

    /**
     * Get an active client by the given ID.
     *
     * @param  int  $id
     * @return \Laravel\Passport\Client|null
     */
    public function findActive($id)
    {
        $client = $this->find($id);

        return $client && ! $client->revoked ? $client : null;
    }

    /**
     * Get a client instance for the given ID and user.
     *
     * @param  int  $clientId
	 * @param  Illuminate\Foundation\Auth\User $user
     * @return \Laravel\Passport\Client|null
     */
    public function findForUser($clientId, $user)
    {
        return Client::where('id', $clientId)
                     ->where('user_id', $user)
                     ->where('user_type', get_class($user))
                     ->first();
    }

    /**
     * Get the client instances for the given user.
     *
	 * @param  Illuminate\Foundation\Auth\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($user)
    {
        return Client::where('user_id', $user->getKey())
                        ->where('user_type', get_class($user))
                        ->orderBy('name', 'asc')->get();
    }

    /**
     * Get the active client instances for the given user.
     *
	 * @param  Illuminate\Foundation\Auth\User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeForUser($user)
    {
        return $this->forUser($user)->reject(function ($client) {
            return $client->revoked;
        })->values();
    }

    /**
     * Get the personal access token client for the application.
     *
     * @return \Laravel\Passport\Client
     */
    public function personalAccessClient()
    {
        if (Passport::$personalAccessClient) {
            return $this->find(Passport::$personalAccessClient);
        }

        return PersonalAccessClient::orderBy('id', 'desc')->first()->client;
    }

    /**
     * Store a new client.
     *
     * @param  int  $userId
     * @param  string  $userType
     * @param  string  $name
     * @param  string  $redirect
     * @param  bool  $personalAccess
     * @param  bool  $password
     * @return \Laravel\Passport\Client
     */
    public function create($userId, $userType, $name, $redirect, $personalAccess = false, $password = false)
    {
        $client = (new Client)->forceFill([
            'user_id' => $userId,
            'user_type' => $userType,
            'name' => $name,
            'secret' => str_random(40),
            'redirect' => $redirect,
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'revoked' => false,
        ]);

        $client->save();

        return $client;
    }

    /**
     * Store a new personal access token client.
     *
	 * @param  Illuminate\Foundation\Auth\User $user
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Client
     */
    public function createPersonalAccessClient($user, $name, $redirect)
    {
		$user_id	= $user !== null ?  $user->getKey() : null;
		$user_type	= $user !== null ?  get_class($user) : null;

        return $this->create($user_id, $user_type, $name, $redirect, true);
    }

    /**
     * Store a new password grant client.
     *
	 * @param  Illuminate\Foundation\Auth\User $user
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Client
     */
    public function createPasswordGrantClient($user, $name, $redirect)
    {
		$user_id	= $user !== null ?  $user->getKey() : null;
		$user_type	= $user !== null ?  get_class($user) : null;

        return $this->create($user_id, $user_type, $name, $redirect, false, true);
    }

    /**
     * Update the given client.
     *
     * @param  Client  $client
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Client
     */
    public function update(Client $client, $name, $redirect)
    {
        $client->forceFill([
            'name' => $name, 'redirect' => $redirect,
        ])->save();

        return $client;
    }

    /**
     * Regenerate the client secret.
     *
     * @param  \Laravel\Passport\Client  $client
     * @return \Laravel\Passport\Client
     */
    public function regenerateSecret(Client $client)
    {
        $client->forceFill([
            'secret' => str_random(40),
        ])->save();

        return $client;
    }

    /**
     * Determine if the given client is revoked.
     *
     * @param  int  $id
     * @return bool
     */
    public function revoked($id)
    {
        $client = $this->find($id);

        return is_null($client) || $client->revoked;
    }

    /**
     * Delete the given client.
     *
     * @param  \Laravel\Passport\Client  $client
     * @return void
     */
    public function delete(Client $client)
    {
        $client->tokens()->update(['revoked' => true]);

        $client->forceFill(['revoked' => true])->save();
    }
}
