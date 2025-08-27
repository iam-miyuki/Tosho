import { Controller } from "@hotwired/stimulus";

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */

export default class extends Controller {
  async getBookInfoJp(e) {
    e.preventDefault();
    const isbn = document.querySelector("#isbn_search").value.trim();
    if (!isbn) {
      alert("Veuillez saisir un ISBN");
      return;
    }

    const openBdUrl = `https://api.openbd.jp/v1/get?isbn=${isbn}`;
    try {
      const response = await fetch(openBdUrl);
      
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }
      const data = await response.json();
      const jpBook = data[0].summary;
      // console.log(jpBook);
      document.querySelector("#book_form_jpTitle").value = jpBook.title;
      document.querySelector("#book_form_jpAuthor").value = jpBook.author;
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des informations du livre :",
        error
      );
    }
  }

  async getBookInfoByISBN(e) {
    e.preventDefault();
    const isbn = document.querySelector("#isbn_search").value.trim();
    const url = `https://openlibrary.org/api/books?bibkeys=ISBN:${isbn}&format=json&jscmd=data`;

    try {
      const response = await fetch(url);
      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }
      const data = await response.json();

     // console.log(data);
      // On récupère les données du livre à partir de l'ISBN
      const bookKey = `ISBN:${isbn}`;
      const bookInfo = data[bookKey];

      if (!bookInfo) {
        console.log("Aucun livre trouvé avec cet ISBN.");
        return;
      }

      // Extraction des informations utiles
      const title = bookInfo.title || "Titre non disponible";
      const authors = bookInfo.authors
        ? bookInfo.authors.map((author) => author.name).join(", ")
        : "Auteur non disponible";
      const coverUrl = bookInfo.cover.small 

      // Affichage des informations
      document.querySelector("#book_form_title").value = title;
      document.querySelector("#book_form_author").value = authors;
      document.querySelector("#book_form_coverUrl").value = coverUrl;
      document.querySelector("img.book-cover").src = coverUrl;
      document.querySelector("img.book-cover").alt = title;

      return {
        title,
        authors,
      };
    } catch (error) {
      console.error(
        "Erreur lors de la récupération des informations du livre :",
        error
      );
    }
  }
}
