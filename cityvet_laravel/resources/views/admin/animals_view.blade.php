@extends('layouts.layout')

@section('content')
  <style>
    /* Base & Reset */
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      background-color: #f0f0f3;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
        Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
      color: #333;
      padding: 2rem;
    }
    h1 {
      font-weight: 600;
      font-size: 1.5rem;
      margin-bottom: 0.25rem;
      color: #444;
    }
    h2 {
      font-weight: 700;
      font-size: 1.7rem;
      margin: 0;
    }
    h3 {
      font-weight: 600;
      font-size: 1rem;
      margin-bottom: 0.75rem;
      color: #777;
    }
    small {
      font-size: 0.85rem;
      color: #666;
    }
    /* Layout */
    .container {
      max-width: 940px;
      margin: 0 auto;
    }
    .title-section {
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .title-section h1 {
      font-weight: 700;
      color: #222;
    }
    .title-section small {
      color: #999;
    }
    .main-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }
    /* Pet Card */
    .pet-card {
      background: #fff;
      border: 2.5px solid #2a91ff;
      border-radius: 12px;
      display: flex;
      padding: 1rem 1.5rem;
      align-items: center;
      gap: 1.5rem;
      flex-wrap: wrap;
      min-height: 180px;
    }
    .pet-type-tag {
      background-color: #ff269e;
      color: white;
      font-weight: 600;
      font-size: 0.85rem;
      padding: 0.25rem 0.85rem;
      border-radius: 9999px;
      position: absolute;
      top: 10px;
      left: 10px;
      user-select: none;
      box-shadow: 0 0 6px rgba(255, 38, 158, 0.35);
    }
    .pet-image {
      position: relative;
      flex-shrink: 0;
      width: 120px;
      height: 130px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 6px 15px rgb(42 145 255 / 0.25);
    }
    .pet-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .qr-code {
      position: absolute;
      bottom: 6px;
      right: 6px;
      width: 34px;
      height: 34px;
      border-radius: 6px;
      background-color: #fff;
      border: 1.5px solid #e0e0e0;
      box-shadow: 0 2px 6px rgb(0 0 0 / 0.12);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .qr-code img {
      width: 22px;
      height: 22px;
    }
    .pet-details {
      flex: 1;
      min-width: 180px;
      font-size: 0.9rem;
      color: #555;
      line-height: 1.4;
    }
    .pet-details dl {
      margin: 0;
    }
    .pet-details dt {
      font-weight: 600;
      color: #222;
      display: inline;
    }
    .pet-details dd {
      display: inline;
      margin: 0 0 0.3rem 4px;
      color: #555;
    }
    /* Owner Card */
    .owner-card {
      background: #fff;
      border-radius: 12px;
      padding: 1.5rem 1.75rem;
      box-shadow: 0 8px 14px rgb(0 0 0 / 0.04);
      min-height: 180px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      font-size: 1rem;
      color: #444;
    }
    .owner-card strong {
      font-weight: 700;
      display: block;
      margin-bottom: 0.15rem;
      color: #111;
    }
    .owner-card small {
      font-weight: 600;
      color: #666;
      opacity: 0.85;
      margin-bottom: 0.45rem;
      display: block;
    }
    .owner-address {
      font-weight: 600;
      color: #555;
      line-height: 1.3;
    }
    /* Pictures Section */
    .pictures-section {
      margin-bottom: 1.5rem;
    }
    .pictures-label {
      font-weight: 600;
      color: #555;
      margin-bottom: 0.75rem;
      font-size: 1rem;
      padding-left: 4px;
    }
    .pictures-list {
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      padding-bottom: 6px;
    }
    .picture-box {
      flex: 0 0 130px;
      height: 180px;
      background-color: #ccc;
      border-radius: 12px;
      box-shadow: inset 0 0 8px #bbb;
      user-select: none;
      position: relative;
    }
    /* Vaccination History Section */
    .vax-history-section {
      margin-top: 0.5rem;
    }
    .vax-label {
      font-weight: 600;
      color: #555;
      margin-bottom: 0.5rem;
      font-size: 1rem;
      padding-left: 4px;
    }
    .vax-textarea {
      width: 100%;
      min-height: 120px;
      border-radius: 12px;
      border: 1.5px solid #ccc;
      padding: 0.9rem 1.1rem;
      font-size: 1rem;
      font-family: inherit;
      resize: vertical;
      box-shadow: inset 0 1px 4px #ddd;
      outline-offset: 2px;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      color: #444;
      background-color: #fff;
    }
    .vax-textarea:focus {
      border-color: #2a91ff;
      box-shadow: 0 0 6px #2a91ffaa;
      background-color: #f9fbff;
    }
    /* Responsive */
    @media (max-width: 680px) {
      .main-grid {
        grid-template-columns: 1fr;
      }
      .owner-card {
        margin-top: 1rem;
      }
      .pet-card {
        justify-content: center;
      }
    }
  </style>
  <div class="container" role="main" aria-label="Animal Profile">

    <header class="title-section" aria-label="Animals category and count">
      <h1>Animals</h1>
      <small aria-live="polite">(Animals: 1)</small>
    </header>

    <section class="main-grid">
      <article class="pet-card" aria-labelledby="pet-name" tabindex="0" style="position:relative;">
        <span class="pet-type-tag" aria-label="Animal type: Pet">Pet</span>
        <figure class="pet-image" aria-label="Image of Mocha, a Golden Retriever dog">
          <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/c7c950cb-f736-4889-8849-ae22b3a4f610.png" alt="Mocha, a Golden Retriever dog sitting, light golden fur, looking forward with friendly expression" onerror="this.style.display='none'" />
          <div class="qr-code" aria-label="QR code related to Mocha">
            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/58ef8c2f-b3ef-41dc-9c22-7cad963926f5.png" alt="QR code black and white pattern" onerror="this.style.display='none'" />
          </div>
        </figure>
        <dl class="pet-details">
          <dt>Name:</dt>
          <dd id="pet-name">Mocha</dd><br />
          <dt>Breed:</dt>
          <dd>Golden Retriever</dd><br />
          <dt>Gender:</dt>
          <dd>Male</dd><br />
          <dt>Birthday:</dt>
          <dd>June 6, 2023</dd><br />
          <dt>Weight:</dt>
          <dd>Unknown</dd><br />
          <dt>Height:</dt>
          <dd>Unknown</dd><br />
          <dt>Vaccines:</dt>
          <dd>–</dd>
        </dl>
      </article>

      <aside class="owner-card" aria-labelledby="owner-name" tabindex="0">
        <h2 id="owner-name">John Doe</h2>
        <small>Owner</small>
        <h3>Address</h3>
        <address class="owner-address">2428 Bugnay, San Vicente, West, Urdaneta</address>
      </aside>
    </section>

    <section class="pictures-section" aria-label="Pictures of Mocha">
      <h3 class="pictures-label">Pictures</h3>
      <div class="pictures-list" role="list">
        <div class="picture-box" role="listitem" aria-label="Placeholder for picture 1"></div>
        <div class="picture-box" role="listitem" aria-label="Placeholder for picture 2"></div>
        <div class="picture-box" role="listitem" aria-label="Placeholder for picture 3"></div>
        <div class="picture-box" role="listitem" aria-label="Placeholder for picture 4"></div>
      </div>
    </section>

    <section class="vax-history-section" aria-label="Vaccination History for Mocha">
      <h3 class="vax-label">Vaccination History</h3>
      <textarea class="vax-textarea" placeholder="No vaccination history available." aria-describedby="vax-note" readonly></textarea>
    </section>

  </div>
@endsection
